<?php

namespace App\Http\Controllers\Accountant;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\Account;
use App\Models\CashBox;
use App\Models\CurrencyConfig;
use App\Models\TypeSpending; 
use App\Models\AccountBalance;
use App\Models\Transaction; 
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $query = Expense::with(['category', 'currency', 'account', 'cashbox', 'creator']);

        if ($request->ajax()) {
            $textColumns = ['id', 'voucher_number', 'manual_voucher', 'note'];
            foreach ($textColumns as $col) {
                if ($request->filled($col)) {
                    $query->where($col, 'like', '%' . $request->input($col) . '%');
                }
            }

            if ($request->filled('date_filter')) {
                $filter = $request->date_filter;
                $today = Carbon::today();

                if ($filter == 'today') {
                    $query->whereDate('expense_date', $today);
                } elseif ($filter == 'yesterday') {
                    $query->whereDate('expense_date', $today->copy()->subDay());
                } elseif ($filter == 'this_month') {
                    $query->whereMonth('expense_date', $today->month)->whereYear('expense_date', $today->year);
                } elseif ($filter == 'last_month') {
                    $lastMonth = $today->copy()->subMonth();
                    $query->whereMonth('expense_date', $lastMonth->month)->whereYear('expense_date', $lastMonth->year);
                } elseif ($filter == 'this_year') { 
                    $query->whereYear('expense_date', $today->year);
                } elseif ($filter == 'last_year') { 
                    $query->whereYear('expense_date', $today->year - 1);
                }
            }

            if ($request->filled('sort')) {
                $query->orderBy($request->sort, $request->input('direction', 'asc'));
            } else {
                $query->latest('expense_date')->latest('id');
            }
        } else {
            $query->latest('expense_date')->latest('id');
        }

        $expenses = $query->paginate(15);

        $expenses->getCollection()->transform(function ($exp) {
            return [
                'id' => $exp->id,
                'expense_date' => $exp->expense_date,
                'voucher_number' => $exp->voucher_number,
                'manual_voucher' => $exp->manual_voucher ?? '-',
                'category_name' => $exp->category ? $exp->category->name : '-', 
                'cash_amount' => $exp->cash_amount,
                'debt_amount' => $exp->debt_amount,
                'discount' => $exp->discount,
                'account_name' => $exp->account ? $exp->account->name : '-',
                'cashbox_name' => $exp->cashbox ? $exp->cashbox->name : '-',
                'currency_name' => $exp->currency ? $exp->currency->currency_type : '-',
                'exchange_rate' => $exp->exchange_rate,
                'note' => $exp->note ?? '-',
                'creator_name' => $exp->creator ? $exp->creator->name : '-',
                'attachment_url' => $exp->attachment ? asset('storage/' . $exp->attachment) : null,
                'edit_url' => route('accountant.expenses.edit', $exp->id),
                'delete_url' => route('accountant.expenses.destroy', $exp->id),
            ];
        });

        if ($request->ajax()) {
            return response()->json($expenses);
        }

        return view('accountant.expenses.index', compact('expenses'));
    }

    public function create()
    {
        $accounts = Account::where('is_active', 1)->get();
        $allBalances = AccountBalance::whereIn('account_id', $accounts->pluck('id'))->get();
        $cashboxes = CashBox::all();
        $currencies = CurrencyConfig::where('is_active', true)->get();
        $categories = TypeSpending::all(); 

        return view('accountant.expenses.create', compact('accounts', 'allBalances', 'cashboxes', 'currencies', 'categories'));
    }

    public function store(Request $request)
    {
        $items = collect($request->items)->filter(function ($item) {
            return !empty($item['category_id']) && !empty($item['price']) && $item['price'] > 0;
        });

        if ($items->isEmpty()) {
            return back()->withInput()->with('error', 'تکایە لانی کەم یەک خەرجی بنووسە (Please add at least one expense).');
        }

        if ($request->debt_amount > 0 && empty($request->account_id)) {
            return back()->withInput()->with('error', 'پێویستە هەژمارێک دیاری بکەیت چونکە بڕی قەرز نووسراوە!');
        }

        if ($request->cash_amount > 0 && empty($request->cashbox_id)) {
            return back()->withInput()->with('error', 'پێویستە قاسەیەک دیاری بکەیت بۆ بڕی نەقد!');
        }

        $request->validate([
            'expense_date' => 'required|date',
            'currency_id' => 'required|exists:currency_configs,id',
            'exchange_rate' => 'required|numeric|min:0.000001',
            'account_id' => 'nullable|exists:accounts,id',
            'cashbox_id' => 'nullable|exists:cash_boxes,id',
            'discount' => 'nullable|numeric|min:0',
            'cash_amount' => 'nullable|numeric|min:0',
            'debt_amount' => 'nullable|numeric|min:0',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:4096',
        ]);

        DB::beginTransaction();

        try {
            $attachmentPath = null;
            if ($request->hasFile('attachment')) {
                $attachmentPath = $request->file('attachment')->store('expenses', 'public');
            }

            $globalDiscount = (float) $request->discount;
            $globalCash = (float) $request->cash_amount;
            $globalDebt = (float) $request->debt_amount;

            // 🟢 سیستەمی نوێی ژمارەی پسوڵە (1, 2, 3 ...)
            $lastExpense = Expense::latest('id')->first();
            $nextVoucher = 1;
            
            if ($lastExpense) {
                // ئەگەر ژمارەکانی پێشوو تەنها ژمارە بن، ئەوە دانەیەکی دەخاتە سەر
                if (is_numeric($lastExpense->voucher_number)) {
                    $nextVoucher = $lastExpense->voucher_number + 1;
                } else {
                    // ئەگەر کۆنەکان هەندێکیان پیتیان تێدابوو (وەک EXP-001)، ئەوا پشت بە ID دەبەستین
                    $nextVoucher = $lastExpense->id + 1;
                }
            }

            foreach ($items as $item) {
                $price = (float) $item['price'];

                $rowDiscount = min($price, $globalDiscount);
                $globalDiscount -= $rowDiscount;
                $priceAfterDiscount = $price - $rowDiscount;

                $rowCash = min($priceAfterDiscount, $globalCash);
                $globalCash -= $rowCash;
                $priceAfterCash = $priceAfterDiscount - $rowCash;

                $rowDebt = min($priceAfterCash, $globalDebt);
                $globalDebt -= $rowDebt;

                // 🟢 تەنها ژمارەکە بەکاردەهێنین و ئینجا یەکێکی دەخەینە سەر بۆ ڕیزی داهاتوو
                $voucherNumber = (string) $nextVoucher++;
                $rowNote = !empty($item['note']) ? $item['note'] : $request->note;

                // ١. تۆمارکردنی خەرجییەکە بە گشتی
                Expense::create([
                    'voucher_number' => $voucherNumber,
                    'manual_voucher' => $request->manual_voucher,
                    'expense_date' => $request->expense_date,
                    'spending_category_id' => $item['category_id'], 
                    'cash_amount' => $rowCash,
                    'debt_amount' => $rowDebt,
                    'discount' => $rowDiscount,
                    'currency_id' => $request->currency_id,
                    'exchange_rate' => $request->exchange_rate,
                    'account_id' => $request->account_id,
                    'cashbox_id' => $request->cashbox_id,
                    'note' => $rowNote, 
                    'attachment' => $attachmentPath,
                    'created_by' => Auth::id(),
                ]);

                // ٢. جوڵەی قەرز (کەمکردنەوەی باڵانسی کەسەکە)
                if ($rowDebt > 0 && $request->account_id) {
                    
                    $accountBalance = AccountBalance::firstOrCreate([
                        'account_id' => $request->account_id,
                        'currency_id' => $request->currency_id,
                    ], ['balance' => 0]);

                    $accountBalance->balance -= $rowDebt; 
                    $accountBalance->save();

                    Transaction::create([
                        'account_id' => $request->account_id,
                        'user_id' => Auth::id(),
                        'currency_id' => $request->currency_id,
                        'type' => 'spending',
                        'amount' => $rowDebt,
                        'total' => $rowDebt,
                        'exchange_rate' => $request->exchange_rate,
                        'manual_date' => $request->expense_date, 
                        'note' => 'قەرز - خەرجی ژمارە: ' . $voucherNumber . ' (' . ($rowNote ?? 'گشتی') . ')',
                        'is_debt' => true,
                    ]);
                }

                // ٣. جوڵەی قاسە (دەرکردنی پارە لە قاسە)
                if ($rowCash > 0 && $request->cashbox_id) {
                    
                    Transaction::create([
                        'cashbox_id' => $request->cashbox_id,
                        'account_id' => $request->account_id, 
                        'user_id' => Auth::id(),
                        'currency_id' => $request->currency_id,
                        'type' => 'spending', 
                        'amount' => $rowCash,
                        'total' => $rowCash,
                        'exchange_rate' => $request->exchange_rate,
                        'manual_date' => $request->expense_date,
                        'note' => 'نەقد - خەرجی ژمارە: ' . $voucherNumber . ' (' . ($rowNote ?? 'گشتی') . ')',
                        'is_debt' => false,
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('accountant.expenses.index')->with('success', 'تۆمارەکە بە سەرکەوتوویی خەزن کرا');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'کێشەیەک ڕوویدا: ' . $e->getMessage());
        }
    }

    public function bulkDelete(Request $request)
    {
        $ids = json_decode($request->ids);
        if (!empty($ids)) {
            Expense::whereIn('id', $ids)->delete();
        }
        return redirect()->route('accountant.expenses.index')->with('success', 'سڕینەوەکە سەرکەوتوو بوو');
    }

    public function destroy($id)
    {
        $expense = Expense::findOrFail($id);
        $expense->delete();
        return redirect()->route('accountant.expenses.index')->with('success', 'سڕینەوەکە سەرکەوتوو بوو');
    }
}