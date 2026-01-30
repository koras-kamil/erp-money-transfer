<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    protected $fillable = ['code', 'city_name', 'branch_id', 'created_by'];

    public function neighborhoods()
    {
        return $this->hasMany(Neighborhood::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}