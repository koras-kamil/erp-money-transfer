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
     * Ensure 'branch_id' and 'created_by' are here so they save correctly.
     */
    protected $fillable = [
        'currency_type',
        'symbol',
        'digit_number',
        'price_total',
        'price_single',
        'price_sell',
        'branch_id',  // Stores the Branch ID (Dropdown Selection)
        'is_active',
        'created_by', // Stores the User ID
    ];

    /**
     * Type Casting
     * Automatically converts database values to PHP types.
     */
    protected $casts = [
        'is_active' => 'boolean',
        'price_total' => 'float',
        'price_single' => 'float',
        'price_sell' => 'float',
        'digit_number' => 'integer',
        'deleted_by', // <--- Add this
    ];

    /**
     * Relationship: Get the Branch associated with this currency.
     * NOTE: Ensure you have deleted the old 'branch' text column from your database 
     * to avoid conflicts with this relationship name.
     */
    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    /**
     * Relationship: Get the User who created this record.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
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

    public function deleter()
{
    return $this->belongsTo(User::class, 'deleted_by');
}
}