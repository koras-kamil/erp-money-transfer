<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // 1. Import SoftDeletes
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class CurrencyConfig extends Model
{
    // 2. Add SoftDeletes to the use statement
    use HasFactory, LogsActivity, SoftDeletes; 

    protected $table = 'currency_configs';

    protected $fillable = [
        'currency_type',
        'symbol',
        'digit_number',
        'price_total',
        'price_single',
        'price_sell',
        'branch',
        'is_active',
    ];

    // 3. Activity Log Configuration
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()              // Tracks all fields in $fillable
            ->logOnlyDirty()        // Only logs if data actually changed
            ->dontSubmitEmptyLogs() // Prevents empty logs
            ->logFillable();        // Important: ensures fillable fields are captured
    }
}