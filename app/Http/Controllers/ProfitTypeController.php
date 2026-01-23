<?php

namespace App\Http\Controllers;

use App\Models\ProfitGroup;
use App\Models\ProfitType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Branch; // <--- Import this
use Mccarlosen\LaravelMpdf\Facades\LaravelMpdf as PDF;

class ProfitTypeController extends Controller
{
    public function index()
    {
        $activeGroups = ProfitGroup::where('is_active', true)->orderBy('name')->get();
        
        // 1. FETCH BRANCHES (Added this)
        $branches = Branch::where('is_active', true)->orderBy('name')->get(); 

        $types = ProfitType::with(['group', 'creator', 'branch'])->orderBy('id', 'asc')->get();
        
        // Pass 'branches' to the view
        return view('profit.type.index', compact('types', 'activeGroups', 'branches'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'types' => 'array', 
            'types.*.name' => 'required', 
            'types.*.profit_group_id' => 'required'
        ]);

        DB::transaction(function () use ($request) {
            foreach ($request->types as $data) {
                
                $saveData = [
                    'profit_group_id' => $data['profit_group_id'],
                    'name'            => $data['name'],
                    'description'     => $data['description'] ?? null,
                    'is_active'       => isset($data['is_active']) ? 1 : 0,
                ];

                // 2. BRANCH LOGIC
                if (isset($data['branch_id'])) {
                    $saveData['branch_id'] = $data['branch_id'];
                } elseif (!isset($data['id'])) {
                    $saveData['branch_id'] = Auth::user()->branch_id; 
                }

                if (isset($data['id']) && $data['id']) {
                    ProfitType::where('id', $data['id'])->update($saveData);
                } else {
                    // NEW ROW
                    $lastCode = ProfitType::withTrashed()
                                ->selectRaw("MAX(CAST(NULLIF(REGEXP_REPLACE(code, '\D', '', 'g'), '') AS INTEGER)) as max_code")
                                ->value('max_code');
                    $nextCode = $lastCode ? ($lastCode + 1) : 1;
                    $saveData['code'] = $nextCode;

                    if (empty($saveData['branch_id'])) {
                        $saveData['branch_id'] = Auth::user()->branch_id;
                    }
                    $saveData['created_by'] = Auth::id();

                    ProfitType::create($saveData);
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

    public function downloadPdf()
    {
        $types = ProfitType::with(['group', 'creator'])->get();
        $data = ['title' => __('profit.types_title'), 'date' => date('Y-m-d H:i'), 'user' => Auth::user()->name ?? 'System', 'rows' => $types];
        $pdf = PDF::loadView('profit.type.pdf', $data, [], ['mode' => 'utf-8', 'format' => 'A4', 'default_font' => 'nrt', 'orientation' => 'P']);
        return $pdf->stream('profit_types.pdf');
    }

    public function trash()
    {
        $types = ProfitType::onlyTrashed()->with(['group', 'deleter'])->orderBy('deleted_at', 'desc')->paginate(10);
        return view('profit.type.trash', compact('types'));
    }

    public function restore($id) { ProfitType::withTrashed()->find($id)->restore(); return back()->with('success', __('profit.type_restored')); }
    public function forceDelete($id) { ProfitType::withTrashed()->find($id)->forceDelete(); return back()->with('success', __('profit.type_permanently_deleted')); }
}