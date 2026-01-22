<?php

namespace App\Http\Controllers;

use App\Models\ProfitGroup;
use App\Models\ProfitType;
use Illuminate\Http\Request;

class ProfitConfigController extends Controller
{
    // --- MAIN PAGE ---
    public function index()
    {
        $groups = ProfitGroup::all();
        // Get active groups for the dropdown
        $activeGroups = ProfitGroup::where('is_active', true)->get();
        $types = ProfitType::with('group')->get();

        return view('profit.index', compact('groups', 'types', 'activeGroups'));
    }

    // --- GROUP ACTIONS ---
    public function storeGroups(Request $request)
    {
        foreach ($request->groups as $group) {
            if (isset($group['id'])) {
                ProfitGroup::where('id', $group['id'])->update($group);
            } else {
                ProfitGroup::create($group);
            }
        }
        // Return with 'active_tab' session so the page reloads on the right tab
        return redirect()->route('profit.index')->with(['success' => 'Groups Saved', 'active_tab' => 'groups']);
    }

    public function destroyGroup($id)
    {
        ProfitGroup::destroy($id);
        return redirect()->route('profit.index')->with(['success' => 'Group Deleted', 'active_tab' => 'groups']);
    }

    // --- TYPE ACTIONS ---
    public function storeTypes(Request $request)
    {
        foreach ($request->types as $type) {
            if (isset($type['id'])) {
                ProfitType::where('id', $type['id'])->update($type);
            } else {
                ProfitType::create($type);
            }
        }
        return redirect()->route('profit.index')->with(['success' => 'Types Saved', 'active_tab' => 'types']);
    }

    public function destroyType($id)
    {
        ProfitType::destroy($id);
        return redirect()->route('profit.index')->with(['success' => 'Type Deleted', 'active_tab' => 'types']);
    }
}