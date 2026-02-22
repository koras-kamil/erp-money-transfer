<?php

namespace App\Http\Controllers\Accountant;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Cashbox;
use App\Models\CurrencyConfig;
use App\Models\Transaction;
use App\Models\ProfitType;
use App\Models\TypeSpending;
use App\Models\AccountBalance; // 🟢 Required to update the user's balance!
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PayingController extends Controller
{
    public function index(Request $request)
    {
        $limit = defined('PER_PAGE') ? PER_PAGE : 20;

        $query = Transaction::with(['account', 'currency', 'cashbox', 'user'])
            ->where('type', 'pay') 
            ->latest();

        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->whereHas('account', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('manual_code', 'like', "%{$search}%");
            });
        }

        $transactions = $query->paginate($limit);

        // 🟢 1. FETCH ALL BALANCES AT ONCE (Super Fast)
        $allBalances = AccountBalance::all()->groupBy('account_id');

        // 🟢 2. PREPARE ACCOUNT DATA & ATTACH BALANCES
        $accounts = Account::with(['city', 'neighborhood'])
            ->where('is_active', true)
            ->get()
            ->map(function ($acc) use ($allBalances) {
                $supported = $acc->supported_currency_ids;
                if (is_string($supported)) {
                    $supported = json_decode($supported, true) ?? [];
                } elseif (!is_array($supported)) {
                    $supported = [];
                }

                $cityName = $acc->city ? ($acc->city->city_name ?? $acc->city->name ?? '') : '';
                $neighborhoodName = $acc->neighborhood ? ($acc->neighborhood->neighborhood_name ?? $acc->neighborhood->name ?? '') : '';

                // Get the balances for this specific account ID
                $accountBalances = $allBalances->get($acc->id, collect());

                return [
                    'id' => $acc->id,
                    'name' => $acc->name,
                    'code' => $acc->manual_code ?? $acc->code,
                    'avatar' => $acc->profile_picture ? asset('storage/'.$acc->profile_picture) : null,
                    'mobile' => $acc->mobile_number_1 ?? '-', 
                    'city_name' => $cityName,
                    'neighborhood_name' => $neighborhoodName,
                    'default_currency_id' => $acc->currency_id, 
                    'supported_currencies' => $supported, 
                    'debt_limit' => number_format($acc->debt_limit ?? 0, 2),
                    
                    // ATTACH BALANCES FOR ALPINE.JS
                    'balances' => $accountBalances->pluck('balance', 'currency_id'),
                ];
            });

        $currencies = CurrencyConfig::where('is_active', true)->get();
        $cashboxes = Cashbox::where('is_active', true)->get();
        
        $profitTypes = ProfitType::all(); 
        $spendingTypes = TypeSpending::all(); 

        return view('accountant.paying.index', compact(
            'transactions', 'accounts', 'cashboxes', 'currencies', 'profitTypes', 'spendingTypes'
        ));
    }

    public function store(Request $request)
    {
        $this->cleanInputs($request);

        $validated = $request->validate([
            'account_id'         => 'required|exists:accounts,id',
            'amount'             => 'required|numeric|min:0',
            'currency_id'        => 'required|exists:currency_configs,id',
            'target_currency_id' => 'required|exists:currency_configs,id', // 🟢 Must validate Target Currency!
            'total'              => 'required|numeric', // 🟢 Must validate Total!
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

        DB::transaction(function () use ($validated) {
            // 1. Create Transaction
            Transaction::create([
                'user_id'            => Auth::id(),
                'type'               => 'pay',
                'account_id'         => $validated['account_id'],
                'currency_id'        => $validated['currency_id'],
                'target_currency_id' => $validated['target_currency_id'], // 🟢 Save Target Currency
                'cashbox_id'         => $validated['cashbox_id'],
                'amount'             => $validated['amount'],
                'total'              => $validated['total'], // 🟢 Save exact calculated total
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

            // 2. 🟢 Update Cashbox Balance (Subtracts the cash)
            $cashbox = Cashbox::lockForUpdate()->find($validated['cashbox_id']);
            if ($cashbox) {
                $cashbox->balance -= $validated['amount'];
                $cashbox->save();
            }

            // 3. 🟢 Update User Account Balance (Paying ADDs (+) to their ledger balance!)
            $accountBalance = AccountBalance::lockForUpdate()->firstOrCreate(
                ['account_id' => $validated['account_id'], 'currency_id' => $validated['target_currency_id']],
                ['balance' => 0]
            );
            $accountBalance->balance += $validated['total']; 
            $accountBalance->save();
        });

        return redirect()->route('accountant.paying.index')->with('success', __('Transaction created successfully.'));
    }

    public function update(Request $request, $id)
    {
        $this->cleanInputs($request);

        $validated = $request->validate([
            'account_id'         => 'required|exists:accounts,id',
            'amount'             => 'required|numeric|min:0',
            'currency_id'        => 'required|exists:currency_configs,id',
            'target_currency_id' => 'required|exists:currency_configs,id',
            'total'              => 'required|numeric',
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

        DB::transaction(function () use ($validated, $id) {
            $transaction = Transaction::findOrFail($id);

            // 1. 🟢 REVERT OLD BALANCES FIRST
            // Revert Old Cashbox
            $oldCashbox = Cashbox::lockForUpdate()->find($transaction->cashbox_id);
            if ($oldCashbox) {
                $oldCashbox->balance += $transaction->amount;
                $oldCashbox->save();
            }
            // Revert Old Account Balance
            $oldTargetCurrency = $transaction->target_currency_id ?? $transaction->currency_id;
            $oldAccountBalance = AccountBalance::lockForUpdate()->where('account_id', $transaction->account_id)->where('currency_id', $oldTargetCurrency)->first();
            if ($oldAccountBalance) {
                $oldAccountBalance->balance -= $transaction->total;
                $oldAccountBalance->save();
            }

            // 2. Update Transaction Data
            $transaction->update([
                'account_id'         => $validated['account_id'],
                'currency_id'        => $validated['currency_id'],
                'target_currency_id' => $validated['target_currency_id'],
                'cashbox_id'         => $validated['cashbox_id'],
                'amount'             => $validated['amount'],
                'total'              => $validated['total'],
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

            // 3. 🟢 APPLY NEW BALANCES
            $newCashbox = Cashbox::lockForUpdate()->find($validated['cashbox_id']);
            if ($newCashbox) {
                $newCashbox->balance -= $validated['amount'];
                $newCashbox->save();
            }

            $newAccountBalance = AccountBalance::lockForUpdate()->firstOrCreate(
                ['account_id' => $validated['account_id'], 'currency_id' => $validated['target_currency_id']],
                ['balance' => 0]
            );
            $newAccountBalance->balance += $validated['total']; 
            $newAccountBalance->save();
        });

        return redirect()->route('accountant.paying.index')->with('success', __('Transaction updated successfully.'));
    }

    public function destroy($id)
    {
        DB::transaction(function () use ($id) {
            $transaction = Transaction::findOrFail($id);
            
            // 1. 🟢 Revert Cashbox (Put money back in drawer)
            $cashbox = Cashbox::lockForUpdate()->find($transaction->cashbox_id);
            if ($cashbox) {
                $cashbox->balance += $transaction->amount;
                $cashbox->save();
            }

            // 2. 🟢 Revert Account Balance (Remove the payment from their ledger)
            $targetCurrencyId = $transaction->target_currency_id ?? $transaction->currency_id;
            $accountBalance = AccountBalance::lockForUpdate()
                ->where('account_id', $transaction->account_id)
                ->where('currency_id', $targetCurrencyId)->first();
            
            if ($accountBalance) {
                $accountBalance->balance -= $transaction->total;
                $accountBalance->save();
            }

            $transaction->delete();
        });
        return redirect()->back()->with('success', __('Transaction deleted successfully.'));
    }

    public function bulkDelete(Request $request)
    {
        // For accurate balances, bulk delete should ideally loop through destroy().
        $ids = json_decode($request->ids, true);
        if (!empty($ids)) {
            foreach($ids as $id) {
                $this->destroy($id); // Safely deletes and reverts balances one by one
            }
            return redirect()->back()->with('success', __('Selected transactions deleted successfully.'));
        }
        return redirect()->back()->with('error', __('No items selected.'));
    }

    public function trash()
    {
        $limit = defined('PER_PAGE') ? PER_PAGE : 20;
        $transactions = Transaction::onlyTrashed()->with(['account', 'user'])->paginate($limit);
        return view('accountant.paying.trash', compact('transactions'));
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