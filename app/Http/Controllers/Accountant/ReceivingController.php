<?php

namespace App\Http\Controllers\Accountant;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Cashbox;
use App\Models\CurrencyConfig;
use App\Models\Transaction;
use App\Models\ProfitType;
use App\Models\TypeSpending;
use App\Models\AccountBalance; // 🟢 IMPORTED ACCOUNT BALANCE
use App\Traits\ManagesAccountBalances;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ReceivingController extends Controller
{
    use ManagesAccountBalances;

    public function index()
    {
        $limit = defined('PER_PAGE') ? PER_PAGE : 10;

        $transactions = Transaction::where('type', 'receive')
            ->with(['account', 'currency', 'cashbox', 'user'])
            ->latest()
            ->paginate($limit);

        // 🟢 Fetch active accounts
        $activeAccounts = Account::with(['city', 'neighborhood'])
            ->where('is_active', true)
            ->get();

        // 🟢 Fetch all balances for these accounts so we can show the exact debt on the form
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

            // 🟢 Group the user's balances by currency_id
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
                'balances' => $accBalances, // 🟢 Passed to frontend!
            ];
        });

        $currencies = CurrencyConfig::where('is_active', true)->get();
        $cashboxes = Cashbox::where('is_active', true)->get(); 
        $profitTypes = ProfitType::all(); 
        $spendingTypes = TypeSpending::all();

        return view('accountant.receiving.index', compact(
            'transactions', 'accounts', 'currencies', 'cashboxes', 'profitTypes', 'spendingTypes'
        ));
    }

    public function store(Request $request)
    {
        $this->cleanInputs($request); 

        $validated = $request->validate([
            'account_id'         => 'required|exists:accounts,id',
            'amount'             => 'required|numeric|min:0',
            'total'              => 'nullable|numeric', 
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
            $totalAmount = $validated['total'] ?? $validated['amount'];

            Transaction::create([
                'user_id'            => Auth::id(),
                'type'               => 'receive',
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
            
            $mainBox = Cashbox::lockForUpdate()->find($validated['cashbox_id']);
            if ($mainBox) { 
                $mainBox->balance += $validated['amount']; 
                $mainBox->save(); 
            }

            $this->updateBalance(
                $validated['account_id'], 
                $finalTargetCurrency, 
                $totalAmount, 
                'receive'
            );

            // ==========================================
            // 2. SAVE PROFIT
            // ==========================================
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
                    'note'          => 'Profit Entry',
                    'manual_date'   => $validated['manual_date'] ?? now(),
                ]);

                if (!$isDebt && $request->profit_cashbox_id) {
                    $box = Cashbox::lockForUpdate()->find($request->profit_cashbox_id);
                    if ($box) { $box->balance += $request->profit_amount; $box->save(); }
                }

                if ($isDebt && $targetUser) {
                    $this->updateBalance($targetUser, $request->profit_currency_id, $request->profit_amount, 'pay');
                }
            }

            // ==========================================
            // 3. SAVE SPENDING
            // ==========================================
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
                    'note'          => 'Spending Entry',
                    'manual_date'   => $validated['manual_date'] ?? now(),
                ]);

                if (!$isDebt && $request->spending_cashbox_id) {
                    $box = Cashbox::lockForUpdate()->find($request->spending_cashbox_id);
                    if ($box) { $box->balance -= $request->spending_amount; $box->save(); }
                }

                if ($isDebt && $targetUser) {
                    $this->updateBalance($targetUser, $request->spending_currency_id, $request->spending_amount, 'pay');
                }
            }
        });

        return redirect()->route('accountant.receiving.index')->with('success', __('Transaction created successfully.'));
    }

    public function update(Request $request, $id)
    {
        $transaction = Transaction::findOrFail($id);
        $this->cleanInputs($request);

        $validated = $request->validate([
            'account_id'         => 'required|exists:accounts,id',
            'amount'             => 'required|numeric|min:0',
            'total'              => 'nullable|numeric', 
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

            // 1. Reverse old cashbox
            $oldBox = Cashbox::lockForUpdate()->find($transaction->cashbox_id);
            if ($oldBox) {
                $oldBox->balance -= $transaction->amount;
                $oldBox->save();
            }
            
            // 2. Reverse OLD Target Balance
            $this->updateBalance($transaction->account_id, $transaction->target_currency_id ?? $transaction->currency_id, $transaction->total, 'pay');

            // 3. Update Transaction
            $newTotal = $validated['total'] ?? ($validated['amount'] + ($validated['discount'] ?? 0));
            
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

            // 4. Apply New Cashbox
            $newBox = Cashbox::lockForUpdate()->find($validated['cashbox_id']);
            if ($newBox) {
                $newBox->balance += $validated['amount'];
                $newBox->save();
            }

            // 5. Apply NEW Target Balance
            $this->updateBalance($validated['account_id'], $finalTargetCurrency, $newTotal, 'receive');

        });

        return redirect()->route('accountant.receiving.index')->with('success', __('Transaction updated successfully.'));
    }

    public function destroy($id)
    {
        DB::transaction(function () use ($id) {
            $transaction = Transaction::findOrFail($id);
            
            $cashbox = Cashbox::lockForUpdate()->find($transaction->cashbox_id);
            if ($cashbox) {
                if ($transaction->type === 'spending') {
                    $cashbox->balance += $transaction->amount;
                } else {
                    $cashbox->balance -= $transaction->amount; 
                }
                $cashbox->save();
            }

            if ($transaction->account_id) {
                $reverseAction = $transaction->type === 'receive' ? 'pay' : 'receive';
                $this->updateBalance($transaction->account_id, $transaction->target_currency_id ?? $transaction->currency_id, $transaction->total, $reverseAction);
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
                    
                    $cashbox = Cashbox::lockForUpdate()->find($transaction->cashbox_id);
                    if ($cashbox) {
                        $cashbox->balance += ($transaction->type === 'spending') ? $transaction->amount : -$transaction->amount;
                        $cashbox->save();
                    }

                    if ($transaction->account_id) {
                        $reverseAction = $transaction->type === 'receive' ? 'pay' : 'receive';
                        $this->updateBalance($transaction->account_id, $transaction->target_currency_id ?? $transaction->currency_id, $transaction->total, $reverseAction);
                    }

                    $transaction->delete();
                }
            });
            return redirect()->back()->with('success', __('Selected transactions deleted successfully.'));
        }
        return redirect()->back()->with('error', __('No items selected.'));
    }

    public function trash()
    {
        $transactions = Transaction::onlyTrashed()->with(['account', 'user'])->paginate(20);
        return view('accountant.receiving.trash', compact('transactions'));
    }

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