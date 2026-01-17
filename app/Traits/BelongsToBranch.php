<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Session;

trait BelongsToBranch  // <--- NAME MUST MATCH FILENAME
{
    protected static function bootBelongsToBranch() // <--- UPDATE THIS TOO
    {
        static::addGlobalScope('branch_filter', function (Builder $builder) {
            if (Session::has('current_branch_id')) {
                $branchId = Session::get('current_branch_id');
                if ($branchId !== 'all') {
                    $builder->where('branch_id', $branchId);
                }
            }
        });

        static::creating(function ($model) {
            if (Session::has('current_branch_id')) {
                $branchId = Session::get('current_branch_id');
                if ($branchId !== 'all') {
                    $model->branch_id = $branchId;
                }
            }
        });
    }
}