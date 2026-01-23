<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProfitGroup extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',         // Needs to be here to save "1", "2"...
        'name',
        'description',
        'branch_id',    // Needs to be here to save the Branch
        'created_by',
        'deleted_by',
        'is_active'
    ];

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
    
    // You don't need the getCodeLabelAttribute anymore 
    // since the code is just a simple number now.
}