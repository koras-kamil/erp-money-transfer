<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

// Models
use App\Models\Branch;
use App\Models\CurrencyConfig;
use App\Models\User;

// Activity Log
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

// Custom Traits
use App\Traits\BelongsToBranch;


class CashBox extends Model
{
    // 2. Add the Trait here
    use HasFactory, SoftDeletes, LogsActivity, BelongsToBranch;

    protected $fillable = [
        'name', 
        'type', 
        'currency_id', 
        'branch_id', // <--- Critical for the Branch Switcher to work
        'user_id', 
        'balance', 
        'description', 
        'date_opened', 
        'is_active'
    ];

    /**
     * Activity Log Options
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'balance', 'is_active', 'branch_id'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    // --- Relationships ---

    public function currency() {
        return $this->belongsTo(CurrencyConfig::class, 'currency_id');
    }

    public function user() {
        return $this->belongsTo(User::class);
    }

    // Note: The 'branch' relationship might also be inside FilterByBranch trait,
    // but keeping it here is safe (Class methods override Trait methods).
    public function branch() {
        return $this->belongsTo(Branch::class);
    }
}