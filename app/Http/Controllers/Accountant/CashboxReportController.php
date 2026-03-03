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

    public function show($id)
    {
        return "This will be the detailed ledger for Cashbox #" . $id;
    }
}