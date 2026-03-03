<?php

namespace App\Http\Controllers\Accountant;

use App\Http\Controllers\Controller;
use App\Models\Cashbox;
use App\Models\CurrencyConfig;
use App\Models\Transaction;
use Illuminate\Http\Request;

class CashboxReportController extends Controller
{
    public function index()
    {
        // 1. Get all active currencies
        $currencies = CurrencyConfig::where('is_active', true)->get();
        
        // 2. Find the Base Currency (پارەی سەرەکی)
        $baseCurrency = $currencies->where('is_default', true)->first() ?? $currencies->first();

        // 3. Fetch all active Cashboxes with their "Opening Balances"
        $cashBoxesRaw = Cashbox::with(['balances.currency', 'branch', 'user'])
            ->where('is_active', true)
            ->get();

        // 🟢 4. THE MAGIC: Fetch the SUM of all transactions grouped by Cashbox & Currency
        // We ignore deleted transactions automatically, and ignore debt (where cashbox is null)
        $transactionSums = Transaction::selectRaw('cashbox_id, currency_id, type, SUM(amount) as total_amount')
            ->whereNotNull('cashbox_id')
            ->groupBy('cashbox_id', 'currency_id', 'type')
            ->get();

        // 5. Calculate the true Live Balance for the frontend grid
        $cashBoxes = $cashBoxesRaw->map(function($box) use ($currencies, $transactionSums) {
            $balances = [];
            
            // Initialize all currencies to 0
            foreach($currencies as $curr) {
                $balances['curr_' . $curr->id] = 0;
            }
            
            // A. Start with the OPENING BALANCE (From your Cashbox definitions)
            foreach($box->balances as $b) {
                $balances['curr_' . $b->currency_id] = floatval($b->balance);
            }

            // B. Add and Subtract all LIVE TRANSACTIONS
            $boxTransactions = $transactionSums->where('cashbox_id', $box->id);
            
            foreach($boxTransactions as $trx) {
                $currId = 'curr_' . $trx->currency_id;
                
                // If the currency doesn't exist in our list, skip it
                if(!isset($balances[$currId])) continue;

                // Money going IN (Add)
                if ($trx->type === 'receive' || $trx->type === 'profit') {
                    $balances[$currId] += floatval($trx->total_amount);
                } 
                // Money going OUT (Subtract)
                elseif ($trx->type === 'pay' || $trx->type === 'spending') {
                    $balances[$currId] -= floatval($trx->total_amount);
                }
            }

            return [
                'id'          => $box->id,
                'name'        => $box->name,
                'branch_name' => $box->branch ? $box->branch->name : '-',
                'user_name'   => $box->user ? $box->user->name : __('System / All'),
                'balances'    => $balances,
            ];
        });

        return view('accountant.cashbox_reports.index', compact('cashBoxes', 'currencies', 'baseCurrency'));
    }

public function show($id, Request $request)
    {
        $cashbox = Cashbox::with(['branch', 'balances'])->findOrFail($id);
        $currencies = CurrencyConfig::where('is_active', true)->get();

        // 1. Calculate TRUE live balances for the Toolbar Cards (Always shows total money)
        $transactionSums = Transaction::selectRaw('currency_id, type, SUM(amount) as total_amount')
            ->where('cashbox_id', $id)
            ->groupBy('currency_id', 'type')
            ->get();

        $liveBalances = [];
        foreach ($currencies as $curr) {
            $bRecord = $cashbox->balances->where('currency_id', $curr->id)->first();
            $balance = $bRecord ? floatval($bRecord->balance) : 0;

            $sums = $transactionSums->where('currency_id', $curr->id);
            foreach ($sums as $trx) {
                if (in_array($trx->type, ['receive', 'profit'])) {
                    $balance += floatval($trx->total_amount);
                } elseif (in_array($trx->type, ['pay', 'spending'])) {
                    $balance -= floatval($trx->total_amount);
                }
            }

            $liveBalances[] = (object) [
                'currency_id' => $curr->id,
                'currency_type' => $curr->currency_type,
                'amount' => $balance
            ];
        }

        // 2. 🟢 Calculate "Brought Forward" Balance (If user selects a Start Date)
        $broughtForward = [];
        foreach ($currencies as $c) { $broughtForward[$c->id] = 0; }

        if ($request->filled('start_date')) {
            $pastSums = Transaction::selectRaw('currency_id, type, SUM(amount) as total_amount')
                ->where('cashbox_id', $id)
                ->whereDate('manual_date', '<', $request->start_date)
                ->groupBy('currency_id', 'type')
                ->get();
                
            foreach ($pastSums as $trx) {
                if (in_array($trx->type, ['receive', 'profit'])) {
                    $broughtForward[$trx->currency_id] += floatval($trx->total_amount);
                } elseif (in_array($trx->type, ['pay', 'spending'])) {
                    $broughtForward[$trx->currency_id] -= floatval($trx->total_amount);
                }
            }
        }

        // 3. Fetch the Ledger Transactions
        $query = Transaction::where('cashbox_id', $id)->with(['account', 'user', 'currency']);

        if ($request->filled('currency_id')) {
            $query->where('currency_id', $request->currency_id);
        }
        if ($request->filled('start_date')) {
            $query->whereDate('manual_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('manual_date', '<=', $request->end_date);
        }

        $transactions = $query->orderBy('manual_date', 'asc')->orderBy('id', 'asc')->paginate(100);

        return view('accountant.cashbox_reports.show', compact('cashbox', 'transactions', 'liveBalances', 'currencies', 'broughtForward'));
    }

}