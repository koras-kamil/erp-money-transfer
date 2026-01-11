<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Branch;          // <--- Make sure this is here
use App\Models\CurrencyConfig;
use Illuminate\Database\Eloquent\SoftDeletes;

class CashBox extends Model
{
    use HasFactory;
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 'type', 'currency_id', 'branch_id', 'user_id', 
        'balance', 'description', 'date_opened', 'is_active'
    ];

    public function currency() {
        return $this->belongsTo(CurrencyConfig::class);
    }

    public function branch() {
        return $this->belongsTo(Branch::class);
    }

    public function user() {
        return $this->belongsTo(User::class);
    }
}