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
        'city_id',          // ✅ Added
        'neighborhood_id',  // ✅ Added
        'location', 
        'debt_limit', 
        'debt_due_time', 
        'is_active', 
        'created_by'
    ];

    public function currency()
    {
        return $this->belongsTo(CurrencyConfig::class, 'currency_id');
    }

    public function city() // ✅ New Relation
    {
        return $this->belongsTo(City::class);
    }

    public function neighborhood() // ✅ New Relation
    {
        return $this->belongsTo(Neighborhood::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}