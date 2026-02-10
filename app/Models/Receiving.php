<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Receiving extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id']; // Allows all other fields to be mass-assigned

    protected $casts = [
        'manual_date'   => 'datetime',
        'amount'        => 'decimal:2',
        'discount'      => 'decimal:2',
        'exchange_rate' => 'decimal:4',
    ];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function currency()
    {
        return $this->belongsTo(CurrencyConfig::class, 'currency_id');
    }

    public function cashbox()
    {
        return $this->belongsTo(CashBox::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // ✅ FIX: Explicitly define 'city_id'
    public function city()
    {
        return $this->belongsTo(City::class, 'city_id');
    }

    // ✅ FIX: Explicitly define 'neighborhood_id'
    public function neighborhood()
    {
        return $this->belongsTo(Neighborhood::class, 'neighborhood_id');
    }
}