<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Traits\LogsActivity; 
use Spatie\Activitylog\LogOptions;

class User extends Authenticatable
{
    // 1. Cleaned up traits (Removed duplicates)
    use HasFactory, Notifiable, HasRoles, LogsActivity;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'branch_id', 
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Define the relationship: A user belongs to a branch.
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Configure Activity Logging.
     * Note: This logs changes to the User table (Name, Email, Branch).
     * Role changes are handled in the Controller because they are in a separate table.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            // Log Name, Email, and Branch ID when they change
            ->logOnly(['name', 'email', 'branch_id']) 
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "User has been {$eventName}");
    }
}