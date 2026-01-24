<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class GroupSpending extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'accountant_code',
        'branch_id',
        'created_by',
        'deleted_by',
        'is_active', // <--- ADD THIS LINE
    ];

    /**
     * Boot function to auto-generate numeric code starting from 1.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            // Get the last record (even deleted ones) to determine the next number
            $latest = static::withTrashed()->orderBy('id', 'desc')->first();
            
            // If no record exists, start at 1. Otherwise, last ID + 1.
            $nextNum = $latest ? ($latest->id + 1) : 1;

            // Set code to strictly numeric string (e.g., "1", "2", "10")
            $model->code = (string) $nextNum;
        });
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'code', 'branch_id', 'accountant_code'])
            ->setDescriptionForEvent(fn(string $eventName) => "Group Spending has been {$eventName}");
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function deleter() {
    return $this->belongsTo(User::class, 'deleted_by');
}
}