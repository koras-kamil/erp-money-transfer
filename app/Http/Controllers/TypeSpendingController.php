<?php

namespace App\Http\Controllers;

use App\Models\TypeSpending;
use App\Models\GroupSpending;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Mccarlosen\LaravelMpdf\Facades\LaravelMpdf as PDF; 

class TypeSpendingController extends Controller
{
    public function index()
    {
        // 1. Fetch Main Data
        $types = TypeSpending::with(['group', 'branch', 'creator'])->orderBy('id')->get();
        
        // 2. Fetch Dropdown Data (Renamed to $groups to fix your error)
        $groups = GroupSpending::all(); 
        $branches = Branch::all(); 
        
        // 3. Pass variables to View
        return view('spending.types.index', compact('types', 'groups', 'branches'));
    }

    public function trash()
    {
        // FIX: Change get() to paginate(10)
        $types = TypeSpending::onlyTrashed()
            ->with(['group', 'branch', 'deleter']) // Eager load relationships
            ->orderBy('deleted_at', 'desc')
            ->paginate(10); // <--- THIS FIXES THE ERROR

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
    $type = TypeSpending::find($id);

    if ($type) {
        $type->update(['deleted_by' => Auth::id()]); // Save User ID
        $type->delete(); // Soft Delete
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
            'date' => date('Y-m-d H:i'),
            'user' => Auth::user()->name ?? 'System',
            'types' => $types
        ];
        
        $pdf = PDF::loadView('spending.types.pdf', $data, [], [
            'mode' => 'utf-8',
            'format' => 'A4',
            'default_font' => 'nrt', 
            'orientation' => 'P',
            'margin_header' => 10,
            'margin_footer' => 10,
        ]);
        
        return $pdf->stream('spending_types.pdf');
    }
}