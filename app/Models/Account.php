<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Account extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code', 
        'manual_code', 
        'name', 
        'secondary_name', 
        'profile_picture',
        'mobile_number_1', 
        'mobile_number_2', 
        'account_type',
        'currency_id', 
        'city_id',
        'neighborhood_id',
        'branch_id',        // ✅ Added (Now matches your DB)
        'location', 
        'debt_limit', 
        'debt_due_time', 
        'is_active', 
        'created_by'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'debt_limit' => 'decimal:2',
        'debt_due_time' => 'integer',
    ];

    public function currency()
    {
        return $this->belongsTo(CurrencyConfig::class, 'currency_id');
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function neighborhood()
    {
        return $this->belongsTo(Neighborhood::class);
    }

    // ✅ Branch Relationship
    public function branch()
    {
        // Ensure you have a Branch model created (App\Models\Branch)
        return $this->belongsTo(Branch::class); 
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}