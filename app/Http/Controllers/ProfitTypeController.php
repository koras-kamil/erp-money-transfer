<?php

namespace App\Http\Controllers;

use App\Models\ProfitType;
use App\Models\ProfitGroup;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Mccarlosen\LaravelMpdf\Facades\LaravelMpdf as PDF;
use Illuminate\Database\QueryException;

class ProfitTypeController extends Controller
{
   public function index()
    {
        // Fetch the main data
        $types = ProfitType::orderBy('id', 'desc')->get();

        // Fetch relation data needed for dropdowns
        $groups = ProfitGroup::where('is_active', true)->get(); // <--- THIS WAS MISSING
        $branches = Branch::all(); 

        // Pass ALL variables to the view
        return view('profit.type.index', compact('types', 'groups', 'branches'));
    }

public function store(Request $request)
{
    // 1. Validation
    $request->validate([
        'types' => 'array',
        'types.*.name' => 'required|string|max:255',
        // CHANGED: 'required' -> 'nullable'
        'types.*.profit_group_id' => 'nullable|exists:profit_groups,id', 
    ]);

    DB::transaction(function () use ($request) {
        if ($request->has('types')) {
            foreach ($request->types as $data) {
                
                // Map 'note' from view to 'description' in DB
                $description = $data['description'] ?? $data['note'] ?? null;

                $saveData = [
                    // Use null coalescing operator (?? null) to handle empty values
                    'profit_group_id' => $data['profit_group_id'] ?? null, 
                    'name'            => $data['name'],
                    'description'     => $description,
                    'is_active'       => isset($data['is_active']) ? 1 : 0,
                ];

                // Branch Logic
                if (isset($data['branch_id']) && $data['branch_id']) {
                    $saveData['branch_id'] = $data['branch_id'];
                } elseif (!isset($data['id'])) {
                    $saveData['branch_id'] = Auth::user()->branch_id; 
                }

                // Update or Create
                if (isset($data['id']) && $data['id']) {
                    ProfitType::where('id', $data['id'])->update($saveData);
                } else {
                    $lastCode = ProfitType::withTrashed()
                        ->selectRaw("MAX(CAST(NULLIF(REGEXP_REPLACE(CAST(code AS TEXT), '\D', '', 'g'), '') AS INTEGER)) as max_code")
                        ->value('max_code');
                    
                    $saveData['code'] = $lastCode ? ($lastCode + 1) : 1;
                    $saveData['created_by'] = Auth::id();
                    
                    if (empty($saveData['branch_id'])) {
                        $saveData['branch_id'] = Auth::user()->branch_id;
                    }

                    ProfitType::create($saveData);
                }
            }
        }
    });

    return redirect()->route('profit.types.index')->with('success', __('profit.types_saved'));
}
    public function destroy($id)
    {
        $type = ProfitType::find($id);
        if ($type) {
            $type->update(['deleted_by' => Auth::id()]);
            $type->delete();
            return back()->with('success', __('profit.type_deleted'));
        }
        return back()->with('error', 'Not Found');
    }

    // --- BULK ACTIONS ---

    public function bulkDelete(Request $request)
    {
        $ids = json_decode($request->input('ids', '[]'), true);
        if (!empty($ids)) {
            foreach($ids as $id) {
                $type = ProfitType::find($id);
                if($type) {
                    $type->update(['deleted_by' => Auth::id()]);
                    $type->delete();
                }
            }
            return back()->with('success', __('profit.deleted_selected'));
        }
        return back()->with('error', __('profit.nothing_selected'));
    }

    public function bulkRestore(Request $request)
    {
        $ids = json_decode($request->input('ids', '[]'), true);
        if (!empty($ids)) {
            ProfitType::onlyTrashed()->whereIn('id', $ids)->restore();
            return back()->with('success', __('profit.restored_selected'));
        }
        return back()->with('error', __('profit.nothing_selected'));
    }

    public function bulkForceDelete(Request $request)
    {
        $ids = json_decode($request->input('ids', '[]'), true);
        if (!empty($ids)) {
            try {
                $items = ProfitType::onlyTrashed()->whereIn('id', $ids)->get();
                foreach($items as $item) $item->forceDelete();
                return back()->with('success', __('profit.permanently_deleted_selected'));
            } catch (QueryException $e) {
                return back()->with('error', __('profit.error'));
            }
        }
        return back()->with('error', __('profit.nothing_selected'));
    }

    // --- TRASH & PDF ---

    public function trash()
    {
        $types = ProfitType::onlyTrashed()->with(['group', 'branch', 'deleter'])->orderBy('deleted_at', 'desc')->paginate(10);
        
        // FIX: Singular 'profit.type.trash'
        return view('profit.type.trash', compact('types'));
    }

    public function restore($id)
    {
        ProfitType::withTrashed()->find($id)->restore();
        return back()->with('success', __('profit.restored'));
    }

    public function forceDelete($id)
    {
        try {
            ProfitType::withTrashed()->find($id)->forceDelete();
            return back()->with('success', __('profit.permanently_deleted'));
        } catch (QueryException $e) {
            return back()->with('error', __('profit.error'));
        }
    }

    public function downloadPdf()
    {
        $types = ProfitType::with(['group', 'branch', 'creator'])->get();
        $data = ['title' => __('profit.types_title'), 'date' => date('Y-m-d H:i'), 'user' => Auth::user()->name, 'rows' => $types];
        
        // FIX: Singular 'profit.type.pdf'
        $pdf = PDF::loadView('profit.type.pdf', $data, [], ['mode' => 'utf-8', 'format' => 'A4', 'default_font' => 'nrt', 'orientation' => 'P']);
        
        return $pdf->stream('profit_types.pdf');
    }
}