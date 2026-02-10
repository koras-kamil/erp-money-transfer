<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class CurrencyConfig extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $table = 'currency_configs';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'currency_type',
        'symbol',
        'digit_number',
        'price_total',
        'price_single',
        'price_sell',
        'branch_id',    // ✅ This is correct
        'is_active',
        'created_by',
        'deleted_by', 
        'math_operator', 
    ];

    /**
     * Type Casting
     * ⚠️ CRITICAL FIX: Added 'branch_id' => 'integer'
     */
    protected $casts = [
        'is_active'     => 'boolean',
        'price_total'   => 'float',
        'price_single'  => 'float',
        'price_sell'    => 'float',
        'digit_number'  => 'integer',
        'branch_id'     => 'integer', // 👈 ADDED THIS. Ensures it matches the <option value="5">
    ];

    /**
     * Relationship: Branch
     */
    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    /**
     * Relationship: User who created this record
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relationship: User who deleted this record (for Trash view)
     */
    public function deleter()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    /**
     * Activity Log Configuration
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()              
            ->logOnlyDirty()        
            ->dontSubmitEmptyLogs() 
            ->logFillable();        
    }
}