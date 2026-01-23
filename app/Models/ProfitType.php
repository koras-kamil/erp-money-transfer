<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProfitType extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',             // <--- Make sure this is added
        'name',
        'profit_group_id',
        'branch_id',        // <--- Make sure this is added
        'description',
        'is_active',
        'created_by',
        'deleted_by'
    ];

    public function group()
    {
        return $this->belongsTo(ProfitGroup::class, 'profit_group_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function deleter()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }
}