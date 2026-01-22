<?php

namespace App\Http\Controllers;

use App\Models\TypeSpending;
use App\Models\GroupSpending;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
// FIX: Use the full namespace for mPDF to avoid "Class not found" errors
use PDF; 

class TypeSpendingController extends Controller
{
    public function index()
    {
        $types = TypeSpending::with(['group', 'branch', 'creator'])->orderBy('id')->get();
        // Use all() to avoid errors if 'is_active' column is missing in DB
        $activeGroups = GroupSpending::all(); 
        $branches = Branch::all(); 
        
        return view('spending.types.index', compact('types', 'activeGroups', 'branches'));
    }

    public function trash()
    {
        $types = TypeSpending::onlyTrashed()->with(['group', 'branch', 'creator'])->latest()->get();
        return view('spending.types.trash', compact('types'));
    }

    public function store(Request $request)
    {
        $request->validate(['types' => 'array']);

        DB::transaction(function () use ($request) {
            $inputs = $request->input('types', []);
            $lastId = TypeSpending::max('id') ?? 0;

            foreach ($inputs as $data) {
                $saveData = [
                    'name'              => $data['name'],
                    'accountant_code'   => $data['accountant_code'] ?? null,
                    'group_spending_id' => $data['group_spending_id'] ?? null,
                    'branch_id'         => $data['branch_id'] ?? null,
                    'note'              => $data['note'] ?? null,
                    'is_active'         => isset($data['is_active']) ? 1 : 0, 
                ];

                if (isset($data['id']) && $data['id']) {
                    $type = TypeSpending::find($data['id']);
                    if ($type) $type->update($saveData);
                } else {
                    if (!empty($data['name'])) {
                        $lastId++;
                        $saveData['code'] = 'TS-' . str_pad($lastId, 4, '0', STR_PAD_LEFT);
                        $saveData['created_by'] = Auth::id();
                        TypeSpending::create($saveData);
                    }
                }
            }
        });

        return back()->with('success', __('spending.saved'));
    }

    public function destroy($id)
    {
        $type = TypeSpending::findOrFail($id);
        $type->delete();
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
        return back()->with('success', __('spending.permanently_deleted'));
    }

    /**
     * GENERATE PDF
     */
   public function downloadPdf()
    {
        // 1. Fetch Data (with relationships)
        $types = TypeSpending::with(['group', 'branch', 'creator'])->get();

        // 2. Prepare Data Array
        $data = [
            'title' => __('spending.type_header'), // Uses your translation file
            'date' => date('Y-m-d H:i'),
            'user' => Auth::user()->name ?? 'System',
            'types' => $types
        ];

        // 3. Load PDF View
        // Make sure this path matches your folder: resources/views/spending/types/pdf.blade.php
        $pdf = PDF::loadView('spending.types.pdf', $data, [], [
            'mode' => 'utf-8',
            'format' => 'A4',
            'default_font' => 'nrt', // MUST match the key in config/pdf.php
            'margin_header' => 10,
            'margin_footer' => 10,
            'orientation' => 'P', // Portrait
        ]);

        // 4. Stream the file (Open in browser)
        return $pdf->stream('type_spending_report.pdf');
    }
}