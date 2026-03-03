<?php

namespace App\Http\Controllers\Accountant;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Cashbox;
use App\Models\CurrencyConfig;
use App\Models\Transaction;
use App\Models\ProfitType;
use App\Models\TypeSpending;
use App\Models\AccountBalance; 
use App\Traits\ManagesAccountBalances;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PayingController extends Controller
{
    use ManagesAccountBalances;

    public function index()
    {
        $limit = defined('PER_PAGE') ? PER_PAGE : 10;

        $transactions = Transaction::where('type', 'pay')
            ->with(['account', 'currency', 'cashbox', 'user'])
            ->latest()
            ->paginate($limit);

        $activeAccounts = Account::with(['city', 'neighborhood'])
            ->where('is_active', true)
            ->get();

        $balances = AccountBalance::whereIn('account_id', $activeAccounts->pluck('id'))
            ->get()
            ->groupBy('account_id');

        $accounts = $activeAccounts->map(function ($acc) use ($balances) {
            $supported = $acc->supported_currency_ids;
            if (is_string($supported)) {
                $supported = json_decode($supported, true) ?? [];
            } elseif (!is_array($supported)) {
                $supported = [];
            }

            $accBalances = [];
            if (isset($balances[$acc->id])) {
                foreach ($balances[$acc->id] as $bal) {
                    $accBalances[$bal->currency_id] = $bal->balance;
                }
            }

            return [
                'id' => $acc->id,
                'name' => $acc->name,
                'code' => $acc->manual_code ?? $acc->code,
                'mobile' => $acc->mobile_number_1, 
                'avatar' => $acc->profile_picture ? asset('storage/'.$acc->profile_picture) : null,
                'city_name' => $acc->city ? $acc->city->city_name : '',
                'neighborhood_name' => $acc->neighborhood ? $acc->neighborhood->neighborhood_name : '',
                'default_currency_id' => $acc->currency_id, 
                'supported_currencies' => $supported, 
                'balances' => $accBalances, 
            ];
        });

        $currencies = CurrencyConfig::where('is_active', true)->get();
        // 🟢 Fetch cashboxes (Balances strictly for initial UI if needed, but not updated here)
        $cashboxes = Cashbox::with('balances')->where('is_active', true)->get(); 
        
        $profitTypes = ProfitType::all(); 
        $spendingTypes = TypeSpending::all();

        return view('accountant.paying.index', compact(
            'transactions', 'accounts', 'currencies', 'cashboxes', 'profitTypes', 'spendingTypes'
        ));
    }

    public function store(Request $request)
    {
        $this->cleanInputs($request); 

        $validated = $request->validate([
            'account_id'         => 'required|exists:accounts,id',
            'amount'             => 'required|numeric|min:0',
            'total'              => 'required|numeric', 
            'currency_id'        => 'required|exists:currency_configs,id', 
            'target_currency_id' => 'nullable|exists:currency_configs,id', 
            'cashbox_id'         => 'required|exists:cash_boxes,id',
            'exchange_rate'      => 'nullable|numeric',
            'discount'           => 'nullable|numeric',
            'manual_date'        => 'nullable|date',
            'statement_id'       => 'nullable|string',
            'note'               => 'nullable|string',
            'giver_name'         => 'nullable|string',
            'giver_mobile'       => 'nullable|string',
            'receiver_name'      => 'nullable|string',
            'receiver_mobile'    => 'nullable|string',
            
            'profit_amount'      => 'nullable|numeric|min:0',
            'profit_category_id' => 'nullable|integer',
            'profit_cashbox_id'  => 'nullable|integer',
            'profit_currency_id' => 'nullable|integer', 
            'profit_account_id'  => 'nullable|exists:accounts,id', 
            
            'spending_amount'      => 'nullable|numeric|min:0',
            'spending_category_id' => 'nullable|integer',
            'spending_cashbox_id'  => 'nullable|integer',
            'spending_currency_id' => 'nullable|integer', 
            'spending_account_id'  => 'nullable|exists:accounts,id', 
        ]);

        DB::transaction(function () use ($validated, $request) {
            
            $finalTargetCurrency = $validated['target_currency_id'] ?? $validated['currency_id'];
            $totalAmount = (float) $validated['total'];

            // 1. Record the Paying Transaction
            Transaction::create([
                'user_id'            => Auth::id(),
                'type'               => 'pay',
                'account_id'         => $validated['account_id'],
                'currency_id'        => $validated['currency_id'],
                'target_currency_id' => $finalTargetCurrency, 
                'cashbox_id'         => $validated['cashbox_id'],
                'amount'             => $validated['amount'],
                'total'              => $totalAmount,
                'exchange_rate'      => $validated['exchange_rate'] ?? 1,
                'discount'           => $validated['discount'] ?? 0,
                'manual_date'        => $validated['manual_date'] ?? now(),
                'statement_id'       => $validated['statement_id'],
                'note'               => $validated['note'],
                'giver_name'         => $validated['giver_name'],
                'giver_mobile'       => $validated['giver_mobile'],
                'receiver_name'      => $validated['receiver_name'],
                'receiver_mobile'    => $validated['receiver_mobile'],
            ]);
            
            // 🟢 Update Account Balance ONLY (User's debt/credit)
            $this->updateBalance(
                $validated['account_id'], 
                $finalTargetCurrency, 
                $totalAmount, 
                'pay'
            );

            // --- PROFIT ---
            if ($request->filled('profit_amount') && $request->profit_amount > 0) {
                $isDebt = $request->has('profit_is_debt');
                $targetUser = $isDebt ? $request->profit_account_id : null; 

                Transaction::create([
                    'user_id'       => Auth::id(),
                    'type'          => 'profit',
                    'category_id'   => $request->profit_category_id,
                    'account_id'    => $targetUser,
                    'cashbox_id'    => $isDebt ? null : $request->profit_cashbox_id,
                    'currency_id'   => $request->profit_currency_id, 
                    'amount'        => $request->profit_amount,
                    'total'         => $request->profit_amount,
                    'is_debt'       => $isDebt,
                    'note'          => 'Profit Entry from Paying',
                    'manual_date'   => $validated['manual_date'] ?? now(),
                ]);

                if ($isDebt && $targetUser) {
                    $this->updateBalance($targetUser, $request->profit_currency_id, $request->profit_amount, 'pay');
                }
            }

            // --- SPENDING ---
            if ($request->filled('spending_amount') && $request->spending_amount > 0) {
                $isDebt = $request->has('spending_is_debt');
                $targetUser = $isDebt ? $request->spending_account_id : null;

                Transaction::create([
                    'user_id'       => Auth::id(),
                    'type'          => 'spending',
                    'category_id'   => $request->spending_category_id,
                    'account_id'    => $targetUser,
                    'cashbox_id'    => $isDebt ? null : $request->spending_cashbox_id,
                    'currency_id'   => $request->spending_currency_id,
                    'amount'        => $request->spending_amount,
                    'total'         => $request->spending_amount,
                    'is_debt'       => $isDebt,
                    'note'          => 'Spending Entry from Paying',
                    'manual_date'   => $validated['manual_date'] ?? now(),
                ]);

                if ($isDebt && $targetUser) {
                    $this->updateBalance($targetUser, $request->spending_currency_id, $request->spending_amount, 'pay');
                }
            }
        });

        return redirect()->route('accountant.paying.index')->with('success', __('Transaction created successfully.'));
    }

    public function update(Request $request, $id)
    {
        $transaction = Transaction::findOrFail($id);
        $this->cleanInputs($request);

        $validated = $request->validate([
            'account_id'         => 'required|exists:accounts,id',
            'amount'             => 'required|numeric|min:0',
            'total'              => 'required|numeric', 
            'currency_id'        => 'required|exists:currency_configs,id',
            'target_currency_id' => 'nullable|exists:currency_configs,id', 
            'cashbox_id'         => 'required|exists:cash_boxes,id',
            'exchange_rate'      => 'nullable|numeric',
            'discount'           => 'nullable|numeric',
            'manual_date'        => 'nullable|date',
            'statement_id'       => 'nullable|string',
            'note'               => 'nullable|string',
            'giver_name'         => 'nullable|string',
            'giver_mobile'       => 'nullable|string',
            'receiver_name'      => 'nullable|string',
            'receiver_mobile'    => 'nullable|string',
        ]);

        DB::transaction(function () use ($validated, $request, $transaction) {
            
            $finalTargetCurrency = $validated['target_currency_id'] ?? $validated['currency_id'];
            
            // 1. Reverse OLD Target Balance for the User (Action 'receive' reverses a 'pay' action)
            $this->updateBalance($transaction->account_id, $transaction->target_currency_id ?? $transaction->currency_id, $transaction->total, 'receive');

            $newTotal = (float) $validated['total'];
            
            // 2. Update Transaction
            $transaction->update([
                'account_id'         => $validated['account_id'],
                'currency_id'        => $validated['currency_id'],
                'target_currency_id' => $finalTargetCurrency,
                'cashbox_id'         => $validated['cashbox_id'],
                'amount'             => $validated['amount'],
                'total'              => $newTotal,
                'exchange_rate'      => $validated['exchange_rate'] ?? 1,
                'discount'           => $validated['discount'] ?? 0,
                'manual_date'        => $validated['manual_date'],
                'statement_id'       => $validated['statement_id'],
                'note'               => $validated['note'],
                'giver_name'         => $validated['giver_name'],
                'giver_mobile'       => $validated['giver_mobile'],
                'receiver_name'      => $validated['receiver_name'],
                'receiver_mobile'    => $validated['receiver_mobile'],
            ]);

            // 3. Apply NEW Target Balance for the User (Action 'pay')
            $this->updateBalance($validated['account_id'], $finalTargetCurrency, $newTotal, 'pay');

        });

        return redirect()->route('accountant.paying.index')->with('success', __('Transaction updated successfully.'));
    }

    public function destroy($id)
    {
        DB::transaction(function () use ($id) {
            $transaction = Transaction::findOrFail($id);

            // Revert Account Balance ONLY
            if ($transaction->account_id) {
                $this->updateBalance($transaction->account_id, $transaction->target_currency_id ?? $transaction->currency_id, $transaction->total, 'receive');
            }

            $transaction->delete();
        });
        
        return redirect()->back()->with('success', __('Transaction deleted successfully.'));
    }

    public function bulkDelete(Request $request)
    {
        $ids = json_decode($request->ids, true);
        if (!empty($ids)) {
            DB::transaction(function() use ($ids) {
                $transactions = Transaction::whereIn('id', $ids)->get();
                foreach($transactions as $transaction) {

                    // Revert Account Balance ONLY
                    if ($transaction->account_id) {
                        $this->updateBalance($transaction->account_id, $transaction->target_currency_id ?? $transaction->currency_id, $transaction->total, 'receive');
                    }

                    $transaction->delete();
                }
            });
            return redirect()->back()->with('success', __('Selected transactions deleted successfully.'));
        }
        return redirect()->back()->with('error', __('No items selected.'));
    }

    // ==========================================
    // 🟢 TRASH / RESTORE / FORCE DELETE METHODS
    // ==========================================

    public function trash()
    {
        $transactions = Transaction::onlyTrashed()->with(['account', 'user'])->paginate(20);
        return view('accountant.paying.trash', compact('transactions'));
    }

    public function restore($id)
    {
        DB::transaction(function () use ($id) {
            $transaction = Transaction::onlyTrashed()->findOrFail($id);

            // Re-apply Account Balance ONLY (Action 'pay')
            if ($transaction->account_id) {
                $this->updateBalance($transaction->account_id, $transaction->target_currency_id ?? $transaction->currency_id, $transaction->total, 'pay');
            }

            $transaction->restore();
        });

        return redirect()->back()->with('success', app()->getLocale() == 'ku' ? 'بە سەرکەوتوویی گەڕێندرایەوە.' : 'Transaction restored successfully.');
    }

    public function forceDelete($id)
    {
        $transaction = Transaction::onlyTrashed()->findOrFail($id);
        $transaction->forceDelete();
        
        return redirect()->back()->with('success', app()->getLocale() == 'ku' ? 'بە یەکجاری سڕدرایەوە.' : 'Transaction permanently deleted.');
    }

    public function bulkRestore(Request $request)
    {
        $ids = json_decode($request->ids, true);
        if (!empty($ids)) {
            DB::transaction(function() use ($ids) {
                $transactions = Transaction::onlyTrashed()->whereIn('id', $ids)->get();
                foreach($transactions as $transaction) {

                    // Re-apply Account Balance ONLY
                    if ($transaction->account_id) {
                        $this->updateBalance($transaction->account_id, $transaction->target_currency_id ?? $transaction->currency_id, $transaction->total, 'pay');
                    }

                    $transaction->restore();
                }
            });
            return redirect()->back()->with('success', app()->getLocale() == 'ku' ? 'زانیارییە دیاریکراوەکان گەڕێندرانەوە.' : 'Selected transactions restored successfully.');
        }
        return redirect()->back()->with('error', __('No items selected.'));
    }

    public function bulkForceDelete(Request $request)
    {
        $ids = json_decode($request->ids, true);
        if (!empty($ids)) {
            Transaction::onlyTrashed()->whereIn('id', $ids)->forceDelete();
            return redirect()->back()->with('success', app()->getLocale() == 'ku' ? 'زانیارییە دیاریکراوەکان بە یەکجاری سڕدرانەوە.' : 'Selected transactions permanently deleted.');
        }
        return redirect()->back()->with('error', __('No items selected.'));
    }

    // ==========================================
    // HELPERS
    // ==========================================

    private function cleanInputs(Request $request)
    {
        $cleanData = $request->all();
        $fields = ['amount', 'discount', 'exchange_rate', 'total', 'profit_amount', 'spending_amount'];
        foreach ($fields as $field) {
            if (isset($cleanData[$field])) {
                $cleanData[$field] = str_replace(',', '', $cleanData[$field]);
            }
        }
        $request->replace($cleanData);
    }
}