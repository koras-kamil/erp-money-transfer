<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class TypeSpending extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'name', 
        'code', 
        'accountant_code', 
        'group_spending_id', // This matches your DB exactly now
        'branch_id', 
        'created_by', 
        'note', 
        'deleted_by', 
        'is_active'
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->code)) {
                $latest = static::withTrashed()->orderBy('id', 'desc')->first();
                $nextNum = $latest ? ($latest->id + 1) : 1;
                $model->code = (string) $nextNum;
            }
        });
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'code', 'group_spending_id'])
            ->setDescriptionForEvent(fn(string $eventName) => "Type Spending has been {$eventName}");
    }

    // Relationships
    public function group() { return $this->belongsTo(GroupSpending::class, 'group_spending_id'); }
    public function branch() { return $this->belongsTo(Branch::class); }
    public function creator() { return $this->belongsTo(User::class, 'created_by'); }
    public function deleter() { return $this->belongsTo(User::class, 'deleted_by'); }
}