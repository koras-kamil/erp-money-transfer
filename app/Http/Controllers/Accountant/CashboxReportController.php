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
        $currencies = CurrencyConfig::where('is_active', true)->get();
        $baseCurrency = $currencies->where('is_default', true)->first() ?? $currencies->first();

        $cashBoxesRaw = Cashbox::with(['balances.currency', 'branch', 'user'])
            ->where('is_active', true)
            ->get();

        $transactionSums = Transaction::selectRaw('cashbox_id, currency_id, type, SUM(amount) as total_amount')
            ->whereNotNull('cashbox_id')
            ->groupBy('cashbox_id', 'currency_id', 'type')
            ->get();

        $cashBoxes = $cashBoxesRaw->map(function($box) use ($currencies, $transactionSums) {
            $balances = [];
            foreach($currencies as $curr) { $balances['curr_' . $curr->id] = 0; }
            
            foreach($box->balances as $b) {
                $balances['curr_' . $b->currency_id] = floatval($b->balance);
            }

            $boxTransactions = $transactionSums->where('cashbox_id', $box->id);
            foreach($boxTransactions as $trx) {
                $currId = 'curr_' . $trx->currency_id;
                if(!isset($balances[$currId])) continue;

                // 🟢 ADD MONEY IN (Includes transfer_in)
                if (in_array($trx->type, ['receive', 'profit', 'transfer_in'])) {
                    $balances[$currId] += floatval($trx->total_amount);
                } 
                // 🔴 SUBTRACT MONEY OUT (Includes transfer_out)
                elseif (in_array($trx->type, ['pay', 'spending', 'transfer_out'])) {
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
                // 🟢 MATH FOR THE TOP CARDS
                if (in_array($trx->type, ['receive', 'profit', 'transfer_in'])) {
                    $balance += floatval($trx->total_amount);
                } elseif (in_array($trx->type, ['pay', 'spending', 'transfer_out'])) {
                    $balance -= floatval($trx->total_amount);
                }
            }

            $liveBalances[] = (object) [
                'currency_id' => $curr->id,
                'currency_type' => $curr->currency_type,
                'amount' => $balance
            ];
        }

        $broughtForward = [];
        foreach ($currencies as $c) { $broughtForward[$c->id] = 0; }

        if ($request->filled('start_date')) {
            $pastSums = Transaction::selectRaw('currency_id, type, SUM(amount) as total_amount')
                ->where('cashbox_id', $id)
                ->whereDate('manual_date', '<', $request->start_date)
                ->groupBy('currency_id', 'type')
                ->get();
                
            foreach ($pastSums as $trx) {
                // 🟢 MATH FOR DATES
                if (in_array($trx->type, ['receive', 'profit', 'transfer_in'])) {
                    $broughtForward[$trx->currency_id] += floatval($trx->total_amount);
                } elseif (in_array($trx->type, ['pay', 'spending', 'transfer_out'])) {
                    $broughtForward[$trx->currency_id] -= floatval($trx->total_amount);
                }
            }
        }

        $query = Transaction::where('cashbox_id', $id)->with(['account', 'user', 'currency']);

        if ($request->filled('currency_id')) { $query->where('currency_id', $request->currency_id); }
        if ($request->filled('start_date')) { $query->whereDate('manual_date', '>=', $request->start_date); }
        if ($request->filled('end_date')) { $query->whereDate('manual_date', '<=', $request->end_date); }

        $transactions = $query->orderBy('manual_date', 'asc')->orderBy('id', 'asc')->paginate(100);

        return view('accountant.cashbox_reports.show', compact('cashbox', 'transactions', 'liveBalances', 'currencies', 'broughtForward'));
    }
}