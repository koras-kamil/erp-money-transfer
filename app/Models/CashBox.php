<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Branch;
use App\Models\CurrencyConfig;
use Illuminate\Database\Eloquent\SoftDeletes;

// Activity Log Imports
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class CashBox extends Model
{
    use LogsActivity, HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 'type', 'currency_id', 'branch_id', 'user_id', 
        'balance', 'description', 'date_opened', 'is_active'
    ];

    /**
     * This is the missing method that caused your error.
     * It tells Laravel HOW to log the CashBox changes.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'balance', 'is_active']) // Log changes to these fields
            ->logOnlyDirty()                            // Only log when data actually changes
            ->dontSubmitEmptyLogs();
    }

    public function currency() {
        return $this->belongsTo(CurrencyConfig::class, 'currency_id');
    }

    public function branch() {
        return $this->belongsTo(Branch::class);
    }

    public function user() {
        return $this->belongsTo(User::class);
    }
}