<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class BranchScope implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        // 1. If nobody is logged in, do nothing
        if (!Auth::check()) {
            return;
        }

        $user = Auth::user();

        // 2. REGULAR STAFF (Locked)
        // If the user has a specific branch_id (e.g., 2), they ONLY see data for Branch 2.
        if ($user->branch_id) {
            $builder->where($model->getTable() . '.branch_id', $user->branch_id);
        }
        
        // 3. SUPER ADMIN (Owner)
        // If user->branch_id is NULL, we check if they selected a branch in the dropdown.
        else {
            $selectedBranch = request('branch_id') ?? Session::get('selected_branch_id');
            
            if ($selectedBranch) {
                // If they selected a branch, show only that branch
                $builder->where($model->getTable() . '.branch_id', $selectedBranch);
                Session::put('selected_branch_id', $selectedBranch);
            }
            // If they selected "All Branches" (empty), we show everything.
            else if (request()->has('branch_id') && request('branch_id') == '') {
                Session::forget('selected_branch_id');
            }
        }
    }
}