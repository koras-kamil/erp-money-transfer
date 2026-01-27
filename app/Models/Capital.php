<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Capital extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'owner_id', 'share_percentage', 'amount', 
        'currency_id', 'exchange_rate', 'balance_usd', 
        'date', 'created_by'
    ];

    // The Shareholder (Owner)
    public function owner() {
        return $this->belongsTo(User::class, 'owner_id');
    }

    // The Currency Used
    public function currency() {
        return $this->belongsTo(CurrencyConfig::class, 'currency_id');
    }

    // The System User who entered the data
    public function creator() {
        return $this->belongsTo(User::class, 'created_by');
    }
}