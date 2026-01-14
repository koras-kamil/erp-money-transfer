<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    public function index()
    {
        // If you have a separate page for branches
        $branches = Branch::all();
        return view('branches.index', compact('branches'));
    }

    public function store(Request $request)
    {
        // 1. Validate Input
        $request->validate([
            'name' => 'required|string|max:191',
            'location' => 'nullable|string|max:191',
        ]);

        // 2. Create Branch
        Branch::create([
            'name' => $request->name,
            'location' => $request->location,
        ]);

        // 3. Redirect back with success message (works with your layout popup)
        return back()->with('success', 'Branch created successfully!');
    }
}