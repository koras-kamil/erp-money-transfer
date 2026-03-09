<?php

namespace App\Http\Controllers\Accountant;

use App\Http\Controllers\Controller;
use App\Models\Cashbox;
use App\Models\CurrencyConfig;
use App\Models\CashboxTransfer;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CashboxTransferController extends Controller
{
    public function index()
    {
        $transfers = CashboxTransfer::with(['fromCashbox', 'toCashbox', 'fromCurrency', 'toCurrency', 'user'])
            ->orderBy('manual_date', 'desc')
            ->paginate(20);
            
        return view('accountant.transfers.index', compact('transfers'));
    }

    public function create()
    {
        $cashboxes = Cashbox::with('balances')->where('is_active', true)->get();
        $currencies = CurrencyConfig::where('is_active', true)->get();
        
        $transactionSums = Transaction::selectRaw('cashbox_id, currency_id, type, SUM(amount) as total_amount')
            ->whereNotNull('cashbox_id')
            ->groupBy('cashbox_id', 'currency_id', 'type')
            ->get();

        $liveBalances = [];
        foreach ($cashboxes as $box) {
            $liveBalances[$box->id] = [];
            foreach ($currencies as $curr) {
                $bRecord = $box->balances->where('currency_id', $curr->id)->first();
                $balance = $bRecord ? floatval($bRecord->balance) : 0;

                $sums = $transactionSums->where('cashbox_id', $box->id)->where('currency_id', $curr->id);
                foreach ($sums as $trx) {
                    if (in_array($trx->type, ['receive', 'profit', 'transfer_in'])) {
                        $balance += floatval($trx->total_amount);
                    } elseif (in_array($trx->type, ['pay', 'spending', 'transfer_out'])) {
                        $balance -= floatval($trx->total_amount);
                    }
                }
                $liveBalances[$box->id][$curr->id] = $balance;
            }
        }
        
        return view('accountant.transfers.create', compact('cashboxes', 'currencies', 'liveBalances'));
    }

    public function store(Request $request)
    {
        // Clean commas from money inputs
        $request->merge([
            'amount_sent' => str_replace(',', '', $request->amount_sent),
            'amount_received' => str_replace(',', '', $request->amount_received),
            'exchange_rate' => str_replace(',', '', $request->exchange_rate),
        ]);

        $validated = $request->validate([
            'from_cashbox_id'  => 'required|exists:cash_boxes,id',
            // 🟢 Allow same cashbox (for currency exchange inside the same drawer)
            'to_cashbox_id'    => 'required|exists:cash_boxes,id', 
            'from_currency_id' => 'required|exists:currency_configs,id',
            'to_currency_id'   => 'required|exists:currency_configs,id',
            'amount_sent'      => 'required|numeric|min:0.01',
            'amount_received'  => 'required|numeric|min:0.01',
            'exchange_rate'    => 'nullable|numeric',
            'manual_date'      => 'required|date',
            'statement_id'     => 'nullable|string',
            'note'             => 'nullable|string',
            'giver_name'       => 'nullable|string|max:255',
            'giver_phone'      => 'nullable|string|max:255',
            'receiver_name'    => 'nullable|string|max:255',
            'receiver_phone'   => 'nullable|string|max:255',
        ]);

        // Prevent sending the exact same currency to the exact same box (Pointless transfer)
        if ($validated['from_cashbox_id'] == $validated['to_cashbox_id'] && $validated['from_currency_id'] == $validated['to_currency_id']) {
            return back()->withErrors(['to_currency_id' => __('You cannot transfer the same currency into the same cashbox.')])->withInput();
        }

        // If currencies are identical, bypass exchange rate
        if ($validated['from_currency_id'] == $validated['to_currency_id']) {
            $validated['amount_received'] = $validated['amount_sent'];
            $validated['exchange_rate']   = 1;
        } else {
            $request->validate(['exchange_rate' => 'required|numeric|min:0.000001']);
        }

        // 🟢 Fetch the Cashbox models so we can get their actual NAMES!
        $fromBox = Cashbox::find($validated['from_cashbox_id']);
        $toBox = Cashbox::find($validated['to_cashbox_id']);

        DB::transaction(function () use ($validated, $fromBox, $toBox) {
            // 1. Save Transfer History
            $transfer = CashboxTransfer::create([
                'from_cashbox_id'  => $validated['from_cashbox_id'],
                'to_cashbox_id'    => $validated['to_cashbox_id'],
                'from_currency_id' => $validated['from_currency_id'],
                'to_currency_id'   => $validated['to_currency_id'],
                'amount_sent'      => $validated['amount_sent'],
                'amount_received'  => $validated['amount_received'],
                'exchange_rate'    => $validated['exchange_rate'],
                'manual_date'      => $validated['manual_date'],
                'statement_id'     => $validated['statement_id'],
                'note'             => $validated['note'],
                'giver_name'       => $validated['giver_name'],
                'giver_phone'      => $validated['giver_phone'],
                'receiver_name'    => $validated['receiver_name'],
                'receiver_phone'   => $validated['receiver_phone'],
                'user_id'          => Auth::id(),
            ]);

            // 2. Ledger Transaction (OUT of Sender Cashbox)
            Transaction::create([
                'user_id'      => Auth::id(),
                'type'         => 'transfer_out',
                'cashbox_id'   => $validated['from_cashbox_id'],
                'currency_id'  => $validated['from_currency_id'],
                'amount'       => $validated['amount_sent'],
                'total'        => $validated['amount_sent'],
                'manual_date'  => $validated['manual_date'],
                'statement_id' => $validated['statement_id'],
                // 🟢 Save the actual NAME instead of the ID
                'note'         => 'Transfer to: ' . $toBox->name . ($validated['note'] ? ' | ' . $validated['note'] : ''),
            ]);

            // 3. Ledger Transaction (IN to Receiver Cashbox)
            Transaction::create([
                'user_id'      => Auth::id(),
                'type'         => 'transfer_in',
                'cashbox_id'   => $validated['to_cashbox_id'],
                'currency_id'  => $validated['to_currency_id'],
                'amount'       => $validated['amount_received'],
                'total'        => $validated['amount_received'],
                'manual_date'  => $validated['manual_date'],
                'statement_id' => $validated['statement_id'],
                // 🟢 Save the actual NAME instead of the ID
                'note'         => 'Transfer from: ' . $fromBox->name . ($validated['note'] ? ' | ' . $validated['note'] : ''),
            ]);
        });

        return redirect()->route('accountant.transfers.index')->with('success', __('Transfer completed successfully.'));
    }
}