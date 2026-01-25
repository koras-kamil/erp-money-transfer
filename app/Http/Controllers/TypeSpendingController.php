<?php

namespace App\Http\Controllers;

use App\Models\TypeSpending;
use App\Models\GroupSpending;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Mccarlosen\LaravelMpdf\Facades\LaravelMpdf as PDF; 
use Illuminate\Database\QueryException; // Make sure this is imported at the top

class TypeSpendingController extends Controller
{
public function index()
{
    $types = TypeSpending::orderBy('id', 'desc')->get();
    $branches = Branch::all();
    
    // FETCH GROUPS
    $groups = GroupSpending::all(); // <--- Add this line

    // PASS GROUPS TO VIEW
    return view('spending.types.index', compact('types', 'branches', 'groups'));
}

   public function store(Request $request)
{
    // 1. Validate
    $request->validate(['types' => 'required|array']);

    DB::transaction(function () use ($request) {
        $inputs = $request->input('types', []);

        foreach ($inputs as $data) {
            // Skip rows with no name
            if (empty($data['name'])) continue;

            $saveData = [
                'name' => $data['name'],
                
                // --- THE FIX: Convert empty strings "" to NULL ---
                'group_spending_id' => !empty($data['group_spending_id']) ? $data['group_spending_id'] : null,
                'branch_id'         => !empty($data['branch_id']) ? $data['branch_id'] : null,
                
                'note'              => $data['note'] ?? null,
                
                // Fixed logic: If checked (isset) = 1, if unchecked = 0
                'is_active'         => isset($data['is_active']) ? 1 : 0, 
            ];

            if (isset($data['id']) && !empty($data['id'])) {
                // Update Existing
                $type = TypeSpending::find($data['id']);
                if ($type) $type->update($saveData);
            } else {
                // Create New
                $saveData['created_by'] = Auth::id();
                TypeSpending::create($saveData);
            }
        }
    });

    return back()->with('success', __('spending.saved'));
}

    public function trash()
    {
        $types = TypeSpending::onlyTrashed()
            ->with(['group', 'branch', 'deleter'])
            ->orderBy('deleted_at', 'desc')
            ->paginate(10);

        return view('spending.types.trash', compact('types'));
    }

    public function destroy($id)
    {
        $type = TypeSpending::find($id);
        if ($type) {
            $type->update(['deleted_by' => Auth::id()]);
            $type->delete();
            return back()->with('success', __('spending.deleted'));
        }
        return back()->with('error', __('spending.not_found'));
    }

    public function restore($id)
    {
        TypeSpending::withTrashed()->findOrFail($id)->restore();
        return back()->with('success', __('spending.restored'));
    }

    public function forceDelete($id)
    {
        TypeSpending::withTrashed()->findOrFail($id)->forceDelete();
        return back()->with('success', __('spending.permanently_deleted'));
    }

    public function downloadPdf()
    {
        $types = TypeSpending::with(['group', 'branch'])->get();
        $data = [
            'title' => __('spending.type_header'),
            'date' => now()->format('Y-m-d H:i'),
            'user' => Auth::user()->name ?? 'System',
            'types' => $types
        ];
        
        $pdf = PDF::loadView('spending.types.pdf', $data, [], [
            'mode' => 'utf-8', 'format' => 'A4', 'default_font' => 'nrt', 
            'orientation' => 'P', 'margin_header' => 10, 'margin_footer' => 10,
        ]);
        
        return $pdf->stream('spending_types.pdf');
    }


public function bulkDelete(Request $request)
{
    $ids = json_decode($request->input('ids', '[]'), true);

    if (!empty($ids) && is_array($ids)) {
        foreach($ids as $id) {
            $type = TypeSpending::find($id);
            if($type) {
                // Optional: Record who deleted it if column exists
                $type->update(['deleted_by' => Auth::id()]);
                $type->delete();
            }
        }
        return back()->with('success', __('spending.deleted_selected'));
    }

    return back()->with('error', __('spending.nothing_selected'));
}

public function bulkRestore(Request $request)
{
    $ids = json_decode($request->input('ids', '[]'), true);

    if (!empty($ids) && is_array($ids)) {
        TypeSpending::onlyTrashed()->whereIn('id', $ids)->restore();
        return back()->with('success', __('spending.restored_selected'));
    }

    return back()->with('error', __('spending.nothing_selected'));
}

public function bulkForceDelete(Request $request)
{
    $ids = json_decode($request->input('ids', '[]'), true);

    if (!empty($ids) && is_array($ids)) {
        try {
            $items = TypeSpending::onlyTrashed()->whereIn('id', $ids)->get();
            foreach($items as $item) {
                $item->forceDelete();
            }
            return back()->with('success', __('spending.permanently_deleted_selected'));
        } catch (QueryException $e) {
            if ($e->getCode() == "23503") {
                return back()->with('error', __('spending.cannot_delete_used_bulk'));
            }
            return back()->with('error', __('spending.error'));
        }
    }

    return back()->with('error', __('spending.nothing_selected'));
}


}