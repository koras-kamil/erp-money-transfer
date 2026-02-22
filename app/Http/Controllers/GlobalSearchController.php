<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Account;
use App\Models\CashBox;
use App\Models\CurrencyConfig;
use App\Models\User;
use App\Models\Branch;
use App\Models\Capital;
use App\Models\TypeSpending;

class GlobalSearchController extends Controller
{
    public function search(Request $request)
    {
        try {
            $q = $request->input('q');
            
            // 🟢 CHANGED: Now it triggers even if you type just 1 letter!
            if (!$q || mb_strlen($q) < 1) {
                return response()->json([]);
            }

            $results = [];

            // 1. Search Accounts
            $accounts = Account::where('name', 'ILIKE', "%{$q}%")
                ->orWhere('code', 'ILIKE', "%{$q}%")
                ->orWhere('mobile_number_1', 'ILIKE', "%{$q}%")
                ->limit(5)->get();
                
            if ($accounts->count() > 0) {
                $results['هەژمارەکان (Accounts)'] = $accounts->map(function($acc) {
                    return [
                        'title' => $acc->name,
                        'subtitle' => 'کۆد: ' . $acc->code . ' | مۆبایل: ' . ($acc->mobile_number_1 ?? '-'),
                        'url' => route('accountant.statement.show', $acc->id) 
                    ];
                });
            }

            // 2. Search Cashboxes
            if (class_exists(\App\Models\CashBox::class)) {
                $cashboxes = \App\Models\CashBox::where('name', 'ILIKE', "%{$q}%")->limit(3)->get();
                if ($cashboxes->count() > 0) {
                    $results['سندوقەکان (Cashboxes)'] = $cashboxes->map(function($box) {
                        return [
                            'title' => $box->name,
                            'subtitle' => 'سندوقی پارە',
                            'url' => route('cash-boxes.show', $box->id)
                        ];
                    });
                }
            }

            // 3. Search Users
            if (class_exists(User::class)) {
                $users = User::where('name', 'ILIKE', "%{$q}%")->orWhere('email', 'ILIKE', "%{$q}%")->limit(3)->get();
                if ($users->count() > 0) {
                    $results['بەکارهێنەران (Users)'] = $users->map(function($user) {
                        return [
                            'title' => $user->name,
                            'subtitle' => 'ئیمەیڵ: ' . $user->email,
                            'url' => route('users.show', $user->id)
                        ];
                    });
                }
            }

            // 4. Search Branches
            if (class_exists(Branch::class)) {
                $branches = Branch::where('name', 'ILIKE', "%{$q}%")->limit(3)->get();
                if ($branches->count() > 0) {
                    $results['لقەکان (Branches)'] = $branches->map(function($branch) {
                        return [
                            'title' => $branch->name,
                            'subtitle' => 'لقی کارکردن',
                            'url' => route('branches.show', $branch->id)
                        ];
                    });
                }
            }

            // 5. Search Currencies
            $currencies = CurrencyConfig::where('currency_type', 'ILIKE', "%{$q}%")
                ->orWhere('symbol', 'ILIKE', "%{$q}%")
                ->limit(3)->get();
                
            if ($currencies->count() > 0) {
                $results['دراوەکان (Currencies)'] = $currencies->map(function($curr) {
                    return [
                        'title' => $curr->currency_type,
                        'subtitle' => 'هێما: ' . $curr->symbol,
                        'url' => route('currency.index')
                    ];
                });
            }

            return response()->json($results);

        } catch (\Exception $e) {
            return response()->json([
                'سستەم (System Error)' => [
                    [
                        'title' => 'Error',
                        'subtitle' => $e->getMessage(),
                        'url' => '#'
                    ]
                ]
            ]);
        }
    }
}