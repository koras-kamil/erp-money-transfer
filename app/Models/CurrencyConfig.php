<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CurrencyConfig extends Model
{
    use HasFactory;

    // This must match your database table name
    protected $table = 'currency_configs';

    // These fields are allowed to be saved via the "Sheet" form
    protected $fillable = [
        'currency_type',
        'symbol',
        'digit_number',
        'price_total',
        'price_single',
        'price_sell',
        'branch',
        'is_active',
    ];
}