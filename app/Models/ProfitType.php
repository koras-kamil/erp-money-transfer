<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProfitType extends Model
{
    protected $guarded = [];

    public function group()
    {
        return $this->belongsTo(ProfitGroup::class, 'profit_group_id');
    }
}