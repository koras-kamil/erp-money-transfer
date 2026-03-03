<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CashBoxBalance extends Model
{
    protected $guarded = [];

    public function currency()
    {
        return $this->belongsTo(CurrencyConfig::class, 'currency_id');
    }

    public function cashBox()
    {
        return $this->belongsTo(CashBox::class);
    }
}