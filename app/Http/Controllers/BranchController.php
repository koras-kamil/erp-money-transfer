<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    /**
     * Display a listing of the branches.
     * (Added from Work Computer)
     */
    public function index()
    {
        $branches = Branch::all();
        return view('branches.index', compact('branches'));
    }

    /**
     * Store a newly created branch in storage.
     * (Logic taken from Home Computer because it has better validation)
     */
    public function store(Request $request)
    {
        // 1. Validate the input
        // We use the 'Home' version because it checks for UNIQUE names
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:branches,name',
            'location' => 'nullable|string|max:255',
        ]);

        // 2. Create the Branch
        Branch::create([
            'name' => $validated['name'],
            'location' => $validated['location'],
            'is_active' => true, // Home version correctly sets this to true
        ]);

        // 3. Return success
        return back()->with('success', 'New branch created successfully!');
    }
}