<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CashboxTransfer extends Model
{
    use HasFactory, SoftDeletes;

    // 🟢 THIS FIXES THE ERROR! It tells Laravel these columns are safe to save.
   protected $fillable = [
        'from_cashbox_id',
        'to_cashbox_id',
        'from_currency_id',
        'to_currency_id',
        'amount_sent',
        'amount_received',
        'exchange_rate',
        'manual_date',
        'statement_id',
        'note',
        'user_id',
        'giver_name',
        'giver_phone',
        'receiver_name',
        'receiver_phone',
    ];

    // ==========================================
    // RELATIONSHIPS
    // ==========================================

    public function fromCashbox()
    {
        return $this->belongsTo(CashBox::class, 'from_cashbox_id');
    }

    public function toCashbox()
    {
        return $this->belongsTo(CashBox::class, 'to_cashbox_id');
    }

    public function fromCurrency()
    {
        return $this->belongsTo(CurrencyConfig::class, 'from_currency_id');
    }

    public function toCurrency()
    {
        return $this->belongsTo(CurrencyConfig::class, 'to_currency_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}