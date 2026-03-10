<?php

namespace App\Http\Controllers\Accountant;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\CurrencyConfig;
use App\Models\AccountTransfer;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AccountTransferController extends Controller
{
    public function index()
    {
        $transfers = AccountTransfer::with(['fromAccount', 'toAccount', 'fromCurrency', 'toCurrency', 'user'])
            ->orderBy('manual_date', 'desc')
            ->paginate(20);
            
        // 🟢 FIXED: Pointing to the new "account" folder structure
        return view('accountant.transfers.account.index', compact('transfers'));
    }

    public function create()
    {
        $accounts = Account::where('is_active', true)->get();
        $currencies = CurrencyConfig::where('is_active', true)->get();
        
        // Calculate LIVE balances for Accounts
        $transactionSums = Transaction::selectRaw('account_id, currency_id, type, SUM(amount) as total_amount')
            ->whereNotNull('account_id')
            ->groupBy('account_id', 'currency_id', 'type')
            ->get();

        $liveBalances = [];
        foreach ($accounts as $account) {
            $liveBalances[$account->id] = [];
            foreach ($currencies as $curr) {
                // Assuming you have an opening balance relation or just starting at 0
                $balance = 0; 

                $sums = $transactionSums->where('account_id', $account->id)->where('currency_id', $curr->id);
                foreach ($sums as $trx) {
                    // Adjust this logic based on how your system defines account balances
                    if (in_array($trx->type, ['pay', 'account_transfer_in'])) {
                        $balance += floatval($trx->total_amount);
                    } elseif (in_array($trx->type, ['receive', 'account_transfer_out'])) {
                        $balance -= floatval($trx->total_amount);
                    }
                }
                $liveBalances[$account->id][$curr->id] = $balance;
            }
        }
        
        // 🟢 FIXED: Pointing to the new "account" folder structure
        return view('accountant.transfers.account.create', compact('accounts', 'currencies', 'liveBalances'));
    }

    public function store(Request $request)
    {
        $request->merge([
            'amount_sent' => str_replace(',', '', $request->amount_sent),
            'amount_received' => str_replace(',', '', $request->amount_received),
            'exchange_rate' => str_replace(',', '', $request->exchange_rate),
        ]);

        $validated = $request->validate([
            'from_account_id'  => 'required|exists:accounts,id',
            'to_account_id'    => 'required|exists:accounts,id', 
            'from_currency_id' => 'required|exists:currency_configs,id',
            'to_currency_id'   => 'required|exists:currency_configs,id',
            'amount_sent'      => 'required|numeric|min:0.01',
            'amount_received'  => 'required|numeric|min:0.01',
            'exchange_rate'    => 'nullable|numeric',
            'manual_date'      => 'required|date',
            'statement_id'     => 'nullable|string',
            'note'             => 'nullable|string',
            'giver_name'       => 'nullable|string|max:255',
            'receiver_name'    => 'nullable|string|max:255',
        ]);

        if ($validated['from_account_id'] == $validated['to_account_id'] && $validated['from_currency_id'] == $validated['to_currency_id']) {
            return back()->withErrors(['to_currency_id' => __('You cannot transfer the same currency into the same account.')])->withInput();
        }

        if ($validated['from_currency_id'] == $validated['to_currency_id']) {
            $validated['amount_received'] = $validated['amount_sent'];
            $validated['exchange_rate']   = 1;
        } else {
            $request->validate(['exchange_rate' => 'required|numeric|min:0.000001']);
        }

        $fromAcc = Account::find($validated['from_account_id']);
        $toAcc = Account::find($validated['to_account_id']);

        DB::transaction(function () use ($validated, $fromAcc, $toAcc) {
            // 1. Save Transfer History
            $transfer = AccountTransfer::create([
                'from_account_id'  => $validated['from_account_id'],
                'to_account_id'    => $validated['to_account_id'],
                'from_currency_id' => $validated['from_currency_id'],
                'to_currency_id'   => $validated['to_currency_id'],
                'amount_sent'      => $validated['amount_sent'],
                'amount_received'  => $validated['amount_received'],
                'exchange_rate'    => $validated['exchange_rate'],
                'manual_date'      => $validated['manual_date'],
                'statement_id'     => $validated['statement_id'],
                'note'             => $validated['note'],
                'giver_name'       => $validated['giver_name'],
                'receiver_name'    => $validated['receiver_name'],
                'user_id'          => Auth::id(),
            ]);

            // 2. Ledger Transaction (OUT of Sender Account)
            Transaction::create([
                'user_id'      => Auth::id(),
                'type'         => 'account_transfer_out',
                'account_id'   => $validated['from_account_id'],
                'currency_id'  => $validated['from_currency_id'],
                'amount'       => $validated['amount_sent'],
                'total'        => $validated['amount_sent'],
                'manual_date'  => $validated['manual_date'],
                'statement_id' => $validated['statement_id'],
                'note'         => 'Transfer to Account: ' . $toAcc->name . ($validated['note'] ? ' | ' . $validated['note'] : ''),
            ]);

            // 3. Ledger Transaction (IN to Receiver Account)
            Transaction::create([
                'user_id'      => Auth::id(),
                'type'         => 'account_transfer_in',
                'account_id'   => $validated['to_account_id'],
                'currency_id'  => $validated['to_currency_id'],
                'amount'       => $validated['amount_received'],
                'total'        => $validated['amount_received'],
                'manual_date'  => $validated['manual_date'],
                'statement_id' => $validated['statement_id'],
                'note'         => 'Transfer from Account: ' . $fromAcc->name . ($validated['note'] ? ' | ' . $validated['note'] : ''),
            ]);
        });

        // Route perfectly matches your web.php definition
        return redirect()->route('account_transfers.index')->with('success', __('Transfer completed successfully.'));
    }
}