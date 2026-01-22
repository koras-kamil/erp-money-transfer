<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProfitGroup extends Model
{
    protected $guarded = [];

    public function types()
    {
        return $this->hasMany(ProfitType::class);
    }
}