<?php

namespace App\Http\Controllers\Accountant;

use App\Http\Controllers\Controller;
use App\Models\Income;
use App\Models\Account;
use App\Models\CashBox;
use App\Models\CurrencyConfig;
use App\Models\ProfitType; // 🟢 مۆدێلی جۆری داهات/قازانج
use App\Models\AccountBalance;
use App\Models\Transaction; 
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class IncomeController extends Controller
{
    public function index(Request $request)
    {
        $query = Income::with(['category', 'currency', 'account', 'cashbox', 'creator']);

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
                    $query->whereDate('income_date', $today);
                } elseif ($filter == 'yesterday') {
                    $query->whereDate('income_date', $today->copy()->subDay());
                } elseif ($filter == 'this_month') {
                    $query->whereMonth('income_date', $today->month)->whereYear('income_date', $today->year);
                } elseif ($filter == 'last_month') {
                    $lastMonth = $today->copy()->subMonth();
                    $query->whereMonth('income_date', $lastMonth->month)->whereYear('income_date', $lastMonth->year);
                } elseif ($filter == 'this_year') { 
                    $query->whereYear('income_date', $today->year);
                } elseif ($filter == 'last_year') { 
                    $query->whereYear('income_date', $today->year - 1);
                }
            }

            if ($request->filled('sort')) {
                $query->orderBy($request->sort, $request->input('direction', 'asc'));
            } else {
                $query->latest('income_date')->latest('id');
            }
        } else {
            $query->latest('income_date')->latest('id');
        }

        $incomes = $query->paginate(15);

        $incomes->getCollection()->transform(function ($inc) {
            return [
                'id' => $inc->id,
                'income_date' => $inc->income_date,
                'voucher_number' => $inc->voucher_number,
                'manual_voucher' => $inc->manual_voucher ?? '-',
                'category_name' => $inc->category ? $inc->category->name : '-', 
                'cash_amount' => $inc->cash_amount,
                'debt_amount' => $inc->debt_amount,
                'discount' => $inc->discount,
                'account_name' => $inc->account ? $inc->account->name : '-',
                'cashbox_name' => $inc->cashbox ? $inc->cashbox->name : '-',
                'currency_name' => $inc->currency ? $inc->currency->currency_type : '-',
                'exchange_rate' => $inc->exchange_rate,
                'note' => $inc->note ?? '-',
                'creator_name' => $inc->creator ? $inc->creator->name : '-',
                'attachment_url' => $inc->attachment ? asset('storage/' . $inc->attachment) : null,
                'edit_url' => route('accountant.incomes.edit', $inc->id),
                'delete_url' => route('accountant.incomes.destroy', $inc->id),
            ];
        });

        if ($request->ajax()) {
            return response()->json($incomes);
        }

        return view('accountant.incomes.index', compact('incomes'));
    }

    public function create()
    {
        $accounts = Account::where('is_active', 1)->get();
        $allBalances = AccountBalance::whereIn('account_id', $accounts->pluck('id'))->get();
        $cashboxes = CashBox::all();
        $currencies = CurrencyConfig::where('is_active', true)->get();
        $categories = ProfitType::all(); // 🟢 هێنانی جۆرەکانی قازانج/داهات

        return view('accountant.incomes.create', compact('accounts', 'allBalances', 'cashboxes', 'currencies', 'categories'));
    }

    public function store(Request $request)
    {
        $items = collect($request->items)->filter(function ($item) {
            return !empty($item['category_id']) && !empty($item['price']) && $item['price'] > 0;
        });

        if ($items->isEmpty()) {
            return back()->withInput()->with('error', 'تکایە لانی کەم یەک داهات بنووسە.');
        }

        if ($request->debt_amount > 0 && empty($request->account_id)) {
            return back()->withInput()->with('error', 'پێویستە هەژمارێک دیاری بکەیت چونکە بڕی قەرز نووسراوە!');
        }

        if ($request->cash_amount > 0 && empty($request->cashbox_id)) {
            return back()->withInput()->with('error', 'پێویستە قاسەیەک دیاری بکەیت بۆ بڕی نەقد!');
        }

        $request->validate([
            'income_date' => 'required|date',
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
                $attachmentPath = $request->file('attachment')->store('incomes', 'public');
            }

            $globalDiscount = (float) $request->discount;
            $globalCash = (float) $request->cash_amount;
            $globalDebt = (float) $request->debt_amount;

            $lastIncome = Income::latest('id')->first();
            $nextVoucher = 1;
            
            if ($lastIncome) {
                if (is_numeric($lastIncome->voucher_number)) {
                    $nextVoucher = $lastIncome->voucher_number + 1;
                } else {
                    $nextVoucher = $lastIncome->id + 1;
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

                $voucherNumber = (string) $nextVoucher++;
                $rowNote = !empty($item['note']) ? $item['note'] : $request->note;

                // ١. تۆمارکردنی داهاتەکە
                Income::create([
                    'voucher_number' => $voucherNumber,
                    'manual_voucher' => $request->manual_voucher,
                    'income_date' => $request->income_date,
                    'profit_category_id' => $item['category_id'], 
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

                // ٢. جوڵەی قەرز 🟢 (پێچەوانە: باڵانسی کەسەکە زیاد دەکات چوونکە قەرزدارمان بووە)
                if ($rowDebt > 0 && $request->account_id) {
                    
                    $accountBalance = AccountBalance::firstOrCreate([
                        'account_id' => $request->account_id,
                        'currency_id' => $request->currency_id,
                    ], ['balance' => 0]);

                    $accountBalance->balance += $rowDebt; // 🟢 پێچەوانە کرایەوە بۆ (+)
                    $accountBalance->save();

                    Transaction::create([
                        'account_id' => $request->account_id,
                        'user_id' => Auth::id(),
                        'currency_id' => $request->currency_id,
                        'type' => 'receive', // 🟢 جۆری هاتوو بۆ حیساب
                        'amount' => $rowDebt,
                        'total' => $rowDebt,
                        'exchange_rate' => $request->exchange_rate,
                        'manual_date' => $request->income_date, 
                        'note' => 'قەرز - داهاتی ژمارە: ' . $voucherNumber . ' (' . ($rowNote ?? 'گشتی') . ')',
                        'is_debt' => true,
                    ]);
                }

                // ٣. جوڵەی قاسە 🟢 (پێچەوانە: پارە دەچێتە ناو قاسە)
                if ($rowCash > 0 && $request->cashbox_id) {
                    
                    Transaction::create([
                        'cashbox_id' => $request->cashbox_id,
                        'account_id' => $request->account_id, 
                        'user_id' => Auth::id(),
                        'currency_id' => $request->currency_id,
                        'type' => 'receive', // 🟢 جۆری هاتوو، ڕاپۆرتەکەت دەیخوێنێتەوە و پارەکە زیاد دەکات
                        'amount' => $rowCash,
                        'total' => $rowCash,
                        'exchange_rate' => $request->exchange_rate,
                        'manual_date' => $request->income_date,
                        'note' => 'نەقد - داهاتی ژمارە: ' . $voucherNumber . ' (' . ($rowNote ?? 'گشتی') . ')',
                        'is_debt' => false,
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('accountant.incomes.index')->with('success', 'تۆمارەکە بە سەرکەوتوویی خەزن کرا');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'کێشەیەک ڕوویدا: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        // هێنانەوەی زانیارییەکانی یەک داهات
        $income = Income::with(['category', 'currency', 'account', 'cashbox', 'creator'])->findOrFail($id);
        
        // لەبەر ئەوەی هێشتا پەڕەی show.blade.php مان دروست نەکردووە، 
        // بۆ ئێستا تەنها داتاکە بە شێوەی JSON دەگەڕێنینەوە یان دەتوانین ڕیدایرێکتی بکەین
        return response()->json($income); 
    }

    public function edit($id)
    {
        $income = Income::findOrFail($id);
        
        // ئەگەر پێویستت بە پەڕەی دەستکاریکردن (Edit) بوو، پێم بڵێ با بۆت دروست بکەم
        return "پەڕەی دەستکاریکردن (Edit) هێشتا دروست نەکراوە لە سیستەمەکەدا.";
    }

    public function update(Request $request, $id)
    {
        // کۆدی تازەکردنەوەی داتاکان لێرە دەنووسرێت (کاتێک پەڕەی Edit مان دروستکرد)
    }
    public function bulkDelete(Request $request)
    {
        $ids = json_decode($request->ids);
        if (!empty($ids)) {
            Income::whereIn('id', $ids)->delete();
        }
        return redirect()->route('accountant.incomes.index')->with('success', 'سڕینەوەکە سەرکەوتوو بوو');
    }

    public function destroy($id)
    {
        $income = Income::findOrFail($id);
        $income->delete();
        return redirect()->route('accountant.incomes.index')->with('success', 'سڕینەوەکە سەرکەوتوو بوو');
    }
}