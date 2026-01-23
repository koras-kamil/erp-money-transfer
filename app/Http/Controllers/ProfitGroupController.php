<?php

namespace App\Http\Controllers;

use App\Models\ProfitGroup;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Mccarlosen\LaravelMpdf\Facades\LaravelMpdf as PDF;

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
            foreach ($request->groups as $data) {
                
                $saveData = [
                    'name'        => $data['name'],
                    'description' => $data['description'] ?? null,
                    'is_active'   => isset($data['is_active']) ? 1 : 0,
                ];

                // 1. BRANCH LOGIC
                if (isset($data['branch_id'])) {
                    $saveData['branch_id'] = $data['branch_id'];
                } elseif (!isset($data['id'])) {
                    $saveData['branch_id'] = Auth::user()->branch_id; 
                }

                // 2. UPDATE OR CREATE
                if (isset($data['id']) && $data['id']) {
                    // UPDATE
                    ProfitGroup::where('id', $data['id'])->update($saveData);

                } else {
                    // CREATE NEW
                    
                    // --- FIX: HANDLE "GRP-" DATA SAFELY ---
                    // 1. REGEXP_REPLACE(code, '\D', '', 'g') -> Removes "GRP-" and leaves "0001"
                    // 2. NULLIF(..., '') -> Handles cases where code might be empty
                    // 3. CAST(... AS INTEGER) -> Converts "0001" to the number 1
                    $lastCode = ProfitGroup::withTrashed()
                                ->selectRaw("MAX(CAST(NULLIF(REGEXP_REPLACE(code, '\D', '', 'g'), '') AS INTEGER)) as max_code")
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
        });

        return redirect()->route('profit.groups.index')->with('success', __('profit.groups_saved'));
    }



    public function destroy($id)
    {
        $group = ProfitGroup::find($id);
        if ($group) {
            $group->update(['deleted_by' => Auth::id()]);
            $group->delete();
            return back()->with('success', __('profit.group_deleted'));
        }
        return back()->with('error', 'Not Found');
    }

    public function downloadPdf()
    {
        $groups = ProfitGroup::with(['branch', 'creator'])->get();
        $data = ['title' => __('profit.groups_title'), 'date' => date('Y-m-d H:i'), 'user' => Auth::user()->name ?? 'System', 'rows' => $groups];
        $pdf = PDF::loadView('profit.group.pdf', $data, [], ['mode' => 'utf-8', 'format' => 'A4', 'default_font' => 'nrt', 'orientation' => 'P']);
        return $pdf->stream('profit_groups.pdf');
    }

    // Trash methods
    public function trash()
    {
        $groups = ProfitGroup::onlyTrashed()->with(['branch', 'deleter'])->orderBy('deleted_at', 'desc')->paginate(10);
        return view('profit.group.trash', compact('groups'));
    }
    
    public function restore($id) { ProfitGroup::withTrashed()->find($id)->restore(); return back()->with('success', __('profit.group_restored')); }
    public function forceDelete($id) { ProfitGroup::withTrashed()->find($id)->forceDelete(); return back()->with('success', __('profit.group_permanently_deleted')); }
}