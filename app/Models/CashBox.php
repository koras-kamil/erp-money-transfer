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
    use HasFactory, SoftDeletes, LogsActivity, BelongsToBranch;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name', 
        'type', 
        'currency_id', 
        'branch_id', 
        'user_id', 
        'balance', 
        'description', // Ensures the Note is saved
        'date_opened', 
        'is_active',   // Ensures the Active Status is saved
        'deleted_by',
    ];

    /**
     * The attributes that should be cast.
     * This converts 'is_active' to a true boolean (true/false) automatically.
     */
    protected $casts = [
        'is_active' => 'boolean',
        'balance' => 'decimal:2',
        'date_opened' => 'datetime',
    ];

    /**
     * Activity Log Options
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'name', 
                'balance', 
                'is_active', 
                'branch_id', 
                'currency_id', 
                'type', 
                'description'
            ])
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

    public function branch() {
        return $this->belongsTo(Branch::class);
    }

    public function deleter()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }
}