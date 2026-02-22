<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountBalance extends Model
{
    // 🟢 THIS LINE IS REQUIRED TO ALLOW SAVING
    protected $fillable = ['account_id', 'currency_id', 'balance'];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function currency()
    {
        return $this->belongsTo(CurrencyConfig::class, 'currency_id');
    }
}