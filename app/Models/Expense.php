<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use HasFactory;

    protected $table = 'expenses';

    // 🟢 ئەم دێڕە کێشەکە بە یەکجاری چارەسەر دەکات!
    // بە لاراڤێڵ دەڵێت "هیچ کۆڵۆمێک بلۆک مەکە و با هەمووی خەزن ببێت"
    protected $guarded = []; 

    // ==========================================
    // 🔗 RELATIONSHIPS
    // ==========================================

    public function category()
    {
        return $this->belongsTo(TypeSpending::class, 'spending_category_id');
    }

    public function currency()
    {
        return $this->belongsTo(CurrencyConfig::class, 'currency_id');
    }

    public function account()
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    public function cashbox()
    {
        return $this->belongsTo(CashBox::class, 'cashbox_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}