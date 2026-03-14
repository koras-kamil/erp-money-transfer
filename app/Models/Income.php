<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Income extends Model
{
    use HasFactory;

    protected $table = 'incomes';

    protected $guarded = []; // ڕێگەدان بە خەزنکردنی هەموو کۆڵۆمەکان بەبێ کێشە

    // ==========================================
    // 🔗 RELATIONSHIPS
    // ==========================================

    public function category()
    {
        // 🟢 ناوی مۆدێلی جۆری قازانجەکەت لێرە دابنێ ئەگەر TypeProfit نییە
        return $this->belongsTo(ProfitType::class, 'profit_category_id');
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