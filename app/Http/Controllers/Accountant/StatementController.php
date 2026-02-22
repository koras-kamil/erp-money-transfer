<?php

namespace App\Http\Controllers\Accountant;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\CurrencyConfig;
use App\Models\Transaction;
use App\Models\AccountBalance;
use Illuminate\Http\Request;

class StatementController extends Controller
{
    public function index(Request $request)
    {
        // 🟢 1. FETCH LIGHTWEIGHT LIST (For the Sidebar Search)
        $search_list = Account::query()
            ->select('id', 'name', 'code', 'profile_picture', 'mobile_number_1', 'account_type', 'secondary_name', 'city_id')
            ->with('city') 
            ->orderBy('updated_at', 'desc')
            ->get();

        // 🟢 2. FIND SELECTED ACCOUNT
        $account = null;

        // Case A: Specific ID clicked
        if ($request->filled('account_id')) {
            $account = Account::with(['city', 'neighborhood'])->find($request->account_id);
        } 
        // Case B: Search text entered (User pressed Enter)
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

        // 🟢 3. PREPARE TRANSACTIONS
        // Eager load targetCurrency as well for accurate mapping
        $trxQuery = Transaction::with(['currency', 'targetCurrency', 'user']); 

        if ($account) {
            // Filter by the specific account if one is selected
            $trxQuery->where('account_id', $account->id);
        }

        // 🔥 FILTERS BY THE CLICKED CURRENCY CARD 🔥
        if ($request->filled('currency_id')) {
            $trxQuery->where(function($q) use ($request) {
                // Matches either the cash currency OR the target debt currency
                $q->where('currency_id', $request->currency_id)
                  ->orWhere('target_currency_id', $request->currency_id);
            });
        }

        // 🔥 TRANSACTION TYPE FILTER (Receive / Pay) 🔥
        if ($request->filled('type')) {
            $trxQuery->where('type', $request->type);
        }

        // Date Filter Logic
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $trxQuery->whereBetween('created_at', [
                $request->start_date . ' 00:00:00', 
                $request->end_date . ' 23:59:59'
            ]);
        }
        

        // Get the paginated results
        $transactions = $trxQuery->latest()->paginate(50)->appends($request->all());

        // 🟢 4. PREPARE ACCOUNT-SPECIFIC DATA (Only if an account is selected)
        $supportedCurrencies = collect();
        $lastMovements = ['sale' => '-', 'payment' => '-', 'purchase' => '-', 'return' => '-'];

        if ($account) {
            // A. Currencies & FAST Balances
            $currencyIds = $account->supported_currency_ids;
            if (is_string($currencyIds)) $currencyIds = json_decode($currencyIds, true) ?? [];
            elseif (!is_array($currencyIds)) $currencyIds = [];

            $supportedCurrencies = CurrencyConfig::whereIn('id', $currencyIds)->get();
            $supportedCurrencies->transform(function ($currency) use ($account) {
                // FETCH FROM NEW LEDGER TABLE
                $balanceRecord = AccountBalance::where('account_id', $account->id)
                    ->where('currency_id', $currency->id)
                    ->first();
                
                $currency->current_balance = $balanceRecord ? $balanceRecord->balance : 0;
                return $currency;
            });

            // B. Last Movements
            $types = ['sale', 'payment', 'purchase', 'return'];
            foreach($types as $type) {
                $lastMovements[$type] = Transaction::where('account_id', $account->id)
                    ->where('type', $type)
                    ->latest()
                    ->value('created_at')?->format('Y/m/d') ?? '-';
            }
        }

        return view('accountant.statement.index', compact('account', 'search_list', 'supportedCurrencies', 'transactions', 'lastMovements'));
    }

    /**
     * 🟢 5. SHOW SPECIFIC ACCOUNT
     */
    public function show($id, Request $request)
    {
        $request->merge(['account_id' => $id]);
        return $this->index($request);
    }
}