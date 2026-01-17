<?php

namespace App\Traits;

use App\Scopes\BranchScope;
use App\Models\Branch;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

trait BelongsToBranch
{
    protected static function booted()
    {
        // 1. Apply the "Guard" Scope we created above
        static::addGlobalScope(new BranchScope);

        // 2. Auto-save the correct branch_id when creating new items
        static::creating(function ($model) {
            $user = Auth::user();
            if ($user) {
                if ($user->branch_id) {
                    // Regular staff: Force their branch ID
                    $model->branch_id = $user->branch_id;
                } elseif (Session::has('selected_branch_id')) {
                    // Admin: Use the branch they are currently viewing
                    $model->branch_id = Session::get('selected_branch_id');
                }
            }
        });
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}