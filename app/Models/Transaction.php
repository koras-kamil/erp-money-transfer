<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    use HasFactory, SoftDeletes;

    // This allows all columns to be mass-assigned (including target_currency_id)
    protected $guarded = [];

    protected $casts = [
        'manual_date' => 'datetime',
    ];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // The original base currency of the cashbox/transaction
    public function currency()
    {
        return $this->belongsTo(CurrencyConfig::class, 'currency_id');
    }

    // 🟢 NEW: The target currency the user selected to store the debt in
    public function targetCurrency()
    {
        return $this->belongsTo(CurrencyConfig::class, 'target_currency_id');
    }

    public function cashbox()
    {
        return $this->belongsTo(Cashbox::class);
    }
}