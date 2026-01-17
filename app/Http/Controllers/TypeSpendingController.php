<?php

namespace App\Http\Controllers;

use App\Models\TypeSpending;
use App\Models\GroupSpending;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TypeSpendingController extends Controller
{
    public function index()
    {
        $types = TypeSpending::with(['group', 'branch', 'creator'])->latest()->get();
        $groups = GroupSpending::all(); // For Dropdown
        $branches = Branch::where('is_active', true)->get();
        
        return view('spending.types.index', compact('types', 'groups', 'branches'));
    }

    public function trash()
    {
        $types = TypeSpending::onlyTrashed()->with(['group', 'branch', 'creator'])->latest()->get();
        return view('spending.types.trash', compact('types'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'types' => 'required|array',
            'types.*.name' => 'required|string|max:255',
            'types.*.code' => 'nullable|string|max:255',
            'types.*.accountant_code' => 'nullable|string',
            'types.*.group_id' => 'nullable|exists:group_spendings,id',
            'types.*.branch_id' => 'required|exists:branches,id',
            'types.*.note' => 'nullable|string',
            'types.*.id' => 'nullable|exists:type_spendings,id',
        ]);

        DB::transaction(function () use ($data) {
            foreach ($data['types'] as $row) {
                if (isset($row['id']) && $row['id']) {
                    TypeSpending::where('id', $row['id'])->update([
                        'name' => $row['name'],
                        // Code is not updated to prevent messing up sequences
                        'accountant_code' => $row['accountant_code'],
                        'group_id' => $row['group_id'],
                        'branch_id' => $row['branch_id'],
                        'note' => $row['note'],
                    ]);
                } else {
                    TypeSpending::create([
                        'name' => $row['name'],
                        'code' => $row['code'], // Model handles "AUTO"
                        'accountant_code' => $row['accountant_code'],
                        'group_id' => $row['group_id'],
                        'branch_id' => $row['branch_id'],
                        'note' => $row['note'],
                        'created_by' => auth()->id(),
                    ]);
                }
            }
        });

        return back()->with('success', __('spending.updated'));
    }

    public function destroy($id)
    {
        TypeSpending::findOrFail($id)->delete();
        return back()->with('success', __('spending.deleted'));
    }

    public function restore($id)
    {
        TypeSpending::withTrashed()->findOrFail($id)->restore();
        return back()->with('success', __('spending.restored'));
    }

    public function forceDelete($id)
    {
        TypeSpending::withTrashed()->findOrFail($id)->forceDelete();
        return back()->with('success', 'Permanently deleted.');
    }
}