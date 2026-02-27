<?php

namespace App\Http\Controllers\Accountant;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\CurrencyConfig;
use App\Models\Transaction;
use App\Models\AccountBalance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StatementController extends Controller
{
    public function index(Request $request)
    {
        // 🟢 1. BIG DATA FIX: LIGHTWEIGHT LIST
        // Limits to 100 so the browser doesn't crash with 10,000+ accounts.
        // Users can still find anyone by typing in the search box and pressing Enter.
        $search_list = Account::query()
            ->select('id', 'name', 'code', 'profile_picture', 'mobile_number_1', 'account_type', 'secondary_name', 'city_id', 'supported_currency_ids', 'currency_id')
            ->where('is_active', true)
            ->with('city:id,city_name') 
            ->orderBy('updated_at', 'desc')
            ->limit(100) 
            ->get();

        // 🟢 2. FIND SELECTED ACCOUNT
        $account = null;

        if ($request->filled('account_id')) {
            $account = Account::with(['city', 'neighborhood'])->find($request->account_id);
        } 
        elseif ($request->filled('search')) {
            $search = $request->search;
            $account = Account::with(['city', 'neighborhood'])
                ->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('code', 'like', "%{$search}%")
                      ->orWhere('mobile_number_1', 'like', "%{$search}%");
                })
                ->first(); 
        }

        // 🟢 3. BIG DATA FIX: PREPARE TRANSACTIONS OPTIMIZED
        $transactions = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 50);

        if ($account) {
            // Eager load only the strictly required columns for speed
            $trxQuery = Transaction::query()
                ->with([
                    'currency:id,currency_type,symbol,price_single', 
                    'targetCurrency:id,currency_type,symbol,price_single', 
                    'user:id,name'
                ])
                ->where('account_id', $account->id);

            // Filters
            if ($request->filled('currency_id')) {
                $trxQuery->where(function($q) use ($request) {
                    $q->where('currency_id', $request->currency_id)
                      ->orWhere('target_currency_id', $request->currency_id);
                });
            }

            if ($request->filled('type')) {
                $trxQuery->where('type', $request->type);
            }

            if ($request->filled('start_date') && $request->filled('end_date')) {
                $trxQuery->whereBetween('created_at', [
                    $request->start_date . ' 00:00:00', 
                    $request->end_date . ' 23:59:59'
                ]);
            }

            // Get the paginated results safely
            $transactions = $trxQuery->latest()->paginate(50)->appends($request->all());
        }

        // 🟢 4. PREPARE ACCOUNT-SPECIFIC DATA
        $supportedCurrencies = collect();
        $lastMovements = [];

        if ($account) {
            $currencyIds = $account->supported_currency_ids;
            if (is_string($currencyIds)) $currencyIds = json_decode($currencyIds, true) ?? [];
            elseif (!is_array($currencyIds)) $currencyIds = [];

            $supportedCurrencies = CurrencyConfig::whereIn('id', $currencyIds)->get();
            $supportedCurrencies->transform(function ($currency) use ($account) {
                $balanceRecord = AccountBalance::where('account_id', $account->id)
                    ->where('currency_id', $currency->id)
                    ->first();
                
                $currency->current_balance = $balanceRecord ? $balanceRecord->balance : 0;
                return $currency;
            });

            // FAST LAST MOVEMENTS
            $movementData = Transaction::where('account_id', $account->id)
                ->select('type', DB::raw('MAX(created_at) as last_date'))
                ->groupBy('type')
                ->pluck('last_date', 'type');

            $lastMovements = [
                'receive'         => isset($movementData['receive']) ? Carbon::parse($movementData['receive'])->format('Y-m-d h:i A') : '-',
                'pay'             => isset($movementData['pay']) ? Carbon::parse($movementData['pay'])->format('Y-m-d h:i A') : '-',
                'sale'            => isset($movementData['sale']) ? Carbon::parse($movementData['sale'])->format('Y-m-d h:i A') : '-',
                'return'          => isset($movementData['return']) ? Carbon::parse($movementData['return'])->format('Y-m-d h:i A') : '-',
                'purchase'        => isset($movementData['purchase']) ? Carbon::parse($movementData['purchase'])->format('Y-m-d h:i A') : '-',
                'purchase_return' => isset($movementData['purchase_return']) ? Carbon::parse($movementData['purchase_return'])->format('Y-m-d h:i A') : '-',
            ];
        }

        return view('accountant.statement.index', compact('account', 'search_list', 'supportedCurrencies', 'transactions', 'lastMovements'));
    }

    public function show($id, Request $request)
    {
        $request->merge(['account_id' => $id]);
        return $this->index($request);
    }
}