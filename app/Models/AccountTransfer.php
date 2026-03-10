<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AccountTransfer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'from_account_id', 'to_account_id', 
        'from_currency_id', 'to_currency_id',
        'amount_sent', 'amount_received', 'exchange_rate', 
        'manual_date', 'statement_id', 'note', 
        'giver_name', 'giver_phone', 'receiver_name', 'receiver_phone', 
        'user_id'
    ];

    protected $casts = [
        'manual_date' => 'datetime',
    ];

    // Relationships
    public function fromAccount() {
        return $this->belongsTo(Account::class, 'from_account_id');
    }

    public function toAccount() {
        return $this->belongsTo(Account::class, 'to_account_id');
    }

    public function fromCurrency() {
        return $this->belongsTo(CurrencyConfig::class, 'from_currency_id');
    }

    public function toCurrency() {
        return $this->belongsTo(CurrencyConfig::class, 'to_currency_id');
    }

    public function user() {
        return $this->belongsTo(User::class);
    }
}