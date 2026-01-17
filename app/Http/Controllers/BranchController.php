<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    /**
     * Display a listing of the branches.
     */
    public function index()
    {
        $branches = Branch::all();
        return view('branches.index', compact('branches'));
    }

    /**
     * Store a newly created branch.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:branches,name',
            'location' => 'nullable|string|max:255',
        ]);

        Branch::create([
            'name' => $validated['name'],
            'location' => $validated['location'],
            'is_active' => true,
        ]);

        return back()->with('success', 'New branch created successfully!');
    }

    /**
     * Switch the active branch for the current session.
     * (NEW FUNCTION)
     */
    public function switch(Request $request)
    {
        $request->validate(['branch_id' => 'required']);

        // Save the selected branch ID to the session
        // If value is "all", it will show data from all branches
        session()->put('current_branch_id', $request->branch_id);

      return back()->with('success', __('messages.switch_success'));
    }
}