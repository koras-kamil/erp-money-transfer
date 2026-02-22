<?php

namespace App\Traits;

use App\Models\AccountBalance;

trait ManagesAccountBalances
{
    /**
     * Updates the user's account balance based on debt logic:
     * Positive (+) = User owes us (Debt)
     * Negative (-) = We owe the user
     *
     * @param int $accountId
     * @param int $currencyId
     * @param float $amount
     * @param string $action 'receive' or 'pay'
     */
    public function updateBalance($accountId, $currencyId, $amount, $action)
    {
        $balanceRecord = AccountBalance::firstOrCreate(
            ['account_id' => $accountId, 'currency_id' => $currencyId],
            ['balance' => 0]
        );

        if ($action === 'receive') {
            // Rule: new_balance = old_balance - receiving_amount
            // (Receiving money LOWERS their debt)
            $balanceRecord->decrement('balance', $amount);
            
        } elseif ($action === 'pay') {
            // Rule: new_balance = old_balance + paying_amount (or profit debt)
            // (Paying them or charging them INCREASES their debt)
            $balanceRecord->increment('balance', $amount);
        }
    }
}