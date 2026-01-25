<?php

namespace App\Http\Controllers;

use App\Models\ProfitGroup;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Mccarlosen\LaravelMpdf\Facades\LaravelMpdf as PDF;
use Illuminate\Database\QueryException; 

class ProfitGroupController extends Controller
{
    public function index()
    {
        $groups = ProfitGroup::with(['branch', 'creator'])
                    ->orderBy('id', 'asc') 
                    ->get();

        $branches = Branch::where('is_active', true)->orderBy('name')->get(); 

        return view('profit.group.index', compact('groups', 'branches'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'groups' => 'array', 
            'groups.*.name' => 'required|string|max:255',
        ]);

        DB::transaction(function () use ($request) {
            if ($request->has('groups')) {
                foreach ($request->groups as $data) {
                    
                    $saveData = [
                        'name'        => $data['name'],
                        'description' => $data['description'] ?? null,
                        'is_active'   => isset($data['is_active']) ? 1 : 0,
                    ];

                    if (isset($data['branch_id'])) {
                        $saveData['branch_id'] = $data['branch_id'];
                    } elseif (!isset($data['id'])) {
                        $saveData['branch_id'] = Auth::user()->branch_id; 
                    }

                    if (isset($data['id']) && $data['id']) {
                        ProfitGroup::where('id', $data['id'])->update($saveData);
                    } else {
                        $lastCode = ProfitGroup::withTrashed()
                                    ->selectRaw("MAX(CAST(NULLIF(REGEXP_REPLACE(CAST(code AS TEXT), '\D', '', 'g'), '') AS INTEGER)) as max_code")
                                    ->value('max_code');
                                    
                        $nextCode = $lastCode ? ($lastCode + 1) : 1;
                        $saveData['code'] = $nextCode; 

                        if (empty($saveData['branch_id'])) {
                            $saveData['branch_id'] = Auth::user()->branch_id;
                        }
                        $saveData['created_by'] = Auth::id();
                        
                        ProfitGroup::create($saveData);
                    }
                }
            }
        });

        return redirect()->route('profit.groups.index')->with('success', __('profit.groups_saved'));
    }

    // --- UPDATED DESTROY METHOD ---
    public function destroy($id)
    {
        $group = ProfitGroup::find($id);
        
        if ($group) {
            // CHECK 1: If it has types, DO NOT DELETE
            if ($group->types()->exists()) {
                // If you want a specific message key, add 'cannot_delete_used' to your lang file
                // Or use: "Cannot delete: This group is used by Profit Types."
                return back()->with('error', __('profit.cannot_delete_used') ?? 'Cannot delete: This group contains Profit Types.');
            }

            $group->update(['deleted_by' => Auth::id()]);
            $group->delete();
            return back()->with('success', __('profit.group_deleted'));
        }
        return back()->with('error', 'Not Found');
    }

    // --- UPDATED BULK DELETE METHOD ---
    public function bulkDelete(Request $request)
    {
        $ids = json_decode($request->input('ids', '[]'), true);
        $deletedCount = 0;
        $skippedCount = 0;

        if (!empty($ids) && is_array($ids)) {
            foreach($ids as $id) {
                $group = ProfitGroup::find($id);
                if($group) {
                    // CHECK 1: If it has types, SKIP
                    if ($group->types()->exists()) {
                        $skippedCount++;
                        continue; // Skip this one
                    }

                    $group->update(['deleted_by' => Auth::id()]); 
                    $group->delete();
                    $deletedCount++;
                }
            }

            // Logic to determine success/error message
            if ($skippedCount > 0 && $deletedCount == 0) {
                return back()->with('error', __('profit.cannot_delete_used_bulk') ?? 'Cannot delete selected groups because they contain Profit Types.');
            } elseif ($skippedCount > 0) {
                return back()->with('warning', __('profit.deleted_partial') ?? "$deletedCount deleted. $skippedCount skipped because they are in use.");
            }

            return back()->with('success', __('profit.deleted_selected'));
        }
        return back()->with('error', __('profit.nothing_selected'));
    }

    public function bulkRestore(Request $request)
    {
        $ids = json_decode($request->input('ids', '[]'), true);

        if (!empty($ids) && is_array($ids)) {
            ProfitGroup::onlyTrashed()->whereIn('id', $ids)->restore();
            return back()->with('success', __('profit.restored_selected'));
        }
        return back()->with('error', __('profit.nothing_selected'));
    }

    public function bulkForceDelete(Request $request)
    {
        $ids = json_decode($request->input('ids', '[]'), true);

        if (!empty($ids) && is_array($ids)) {
            try {
                $items = ProfitGroup::onlyTrashed()->whereIn('id', $ids)->get();
                foreach($items as $item) {
                    $item->forceDelete();
                }
                return back()->with('success', __('profit.permanently_deleted_selected'));
            } catch (QueryException $e) {
                if ($e->getCode() == "23503") {
                    return back()->with('error', __('profit.cannot_delete_used'));
                }
                return back()->with('error', __('profit.error'));
            }
        }
        return back()->with('error', __('profit.nothing_selected'));
    }

    // --- TRASH & PDF ---

    public function trash()
    {
        $groups = ProfitGroup::onlyTrashed()
                    ->with(['branch', 'deleter'])
                    ->orderBy('deleted_at', 'desc')
                    ->paginate(10);
                    
        return view('profit.group.trash', compact('groups'));
    }
    
    public function restore($id) 
    { 
        ProfitGroup::withTrashed()->find($id)->restore(); 
        return back()->with('success', __('profit.group_restored')); 
    }

    public function forceDelete($id) 
    { 
        try {
            ProfitGroup::withTrashed()->find($id)->forceDelete(); 
            return back()->with('success', __('profit.group_permanently_deleted')); 
        } catch (QueryException $e) {
            if ($e->getCode() == "23503") {
                return back()->with('error', __('profit.cannot_delete_used'));
            }
            return back()->with('error', __('profit.error'));
        }
    }

    public function downloadPdf()
    {
        $groups = ProfitGroup::with(['branch', 'creator'])->get();
        $data = [
            'title' => __('profit.groups_title'), 
            'date' => date('Y-m-d H:i'), 
            'user' => Auth::user()->name ?? 'System', 
            'rows' => $groups
        ];
        
        $pdf = PDF::loadView('profit.group.pdf', $data, [], [
            'mode' => 'utf-8', 
            'format' => 'A4', 
            'default_font' => 'nrt', 
            'orientation' => 'P'
        ]);
        
        return $pdf->stream('profit_groups.pdf');
    }
}