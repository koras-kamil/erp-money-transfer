<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Account extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    protected $casts = [
        'is_active' => 'boolean',
        'debt_limit' => 'decimal:2',
        'debt_due_time' => 'integer',
        'supported_currency_ids' => 'array', // ✅ Ensure this line has a comma before it
    ];

    public function city() { return $this->belongsTo(City::class, 'city_id'); }
    public function neighborhood() { return $this->belongsTo(Neighborhood::class, 'neighborhood_id'); }
    public function currency() { return $this->belongsTo(CurrencyConfig::class, 'currency_id'); }
    public function branch() { return $this->belongsTo(Branch::class, 'branch_id'); }
    public function creator() { return $this->belongsTo(User::class, 'created_by'); }

    public function supportsCurrency($currencyId)
    {
        return in_array($currencyId, $this->supported_currency_ids ?? []);
    }

    public function balances()
    {
        return $this->hasMany(AccountBalance::class);
    }
}