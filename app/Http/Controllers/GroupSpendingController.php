<?php

namespace App\Http\Controllers;

use App\Models\GroupSpending;
use App\Models\TypeSpending; // <--- ADD THIS IMPORT
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Mccarlosen\LaravelMpdf\Facades\LaravelMpdf as PDF;

class GroupSpendingController extends Controller
{
    public function index()
    {
        $groups = GroupSpending::with(['branch', 'creator'])->orderBy('id')->get();
        $branches = Branch::all();
        
        return view('spending.groups.index', compact('groups', 'branches'));
    }

    public function trash()
    {
        $groups = GroupSpending::onlyTrashed()
            ->with(['branch', 'deleter'])
            ->orderBy('deleted_at', 'desc')
            ->paginate(10); 

        return view('spending.groups.trash', compact('groups'));
    }

    public function store(Request $request)
    {
        $request->validate(['spendings' => 'required|array']);

        DB::transaction(function () use ($request) {
            $inputs = $request->input('spendings', []);

            foreach ($inputs as $data) {
                if (empty($data['name'])) continue;

                $saveData = [
                    'name'      => $data['name'],
                    'branch_id' => $data['branch_id'] ?? null,
                    'is_active' => isset($data['is_active']) ? 1 : 0, 
                ];

                if (isset($data['id']) && $data['id']) {
                    $group = GroupSpending::find($data['id']);
                    if ($group) {
                        $group->update($saveData);
                    }
                } else {
                    $saveData['created_by'] = Auth::id();
                    GroupSpending::create($saveData);
                }
            }
        });

        return back()->with('success', __('spending.saved'));
    }

    /**
     * Destroy Group only if NOT used in TypeSpending
     */
    public function destroy($id)
    {
        $group = GroupSpending::find($id);
        
        if (!$group) {
            return back()->with('error', __('spending.not_found'));
        }

        // --- NEW CHECK: Prevent delete if used in TypeSpending ---
        // We check if any TypeSpending exists with this group_id
        $isUsed = TypeSpending::where('group_spending_id', $id)->exists();

        if ($isUsed) {
            // Return error if it is in use
            return back()->with('error', __('spending.cannot_delete_used')); 
        }
        // ---------------------------------------------------------

        $group->update(['deleted_by' => Auth::id()]); 
        $group->delete(); 
        
        return back()->with('success', __('spending.deleted'));
    }

    public function restore($id)
    {
        $group = GroupSpending::withTrashed()->find($id);
        if ($group) {
            $group->restore();
            return back()->with('success', __('spending.restored'));
        }
        return back()->with('error', __('spending.not_found'));
    }

    public function forceDelete($id)
    {
        $group = GroupSpending::withTrashed()->find($id);
        if ($group) {
            $group->forceDelete();
            return back()->with('success', __('spending.permanently_deleted'));
        }
        return back()->with('error', __('spending.not_found'));
    }

    public function downloadPdf()
    {
        $groups = GroupSpending::with(['branch'])->get();
        $data = [
            'title' => __('spending.group_title'),
            'date' => date('Y-m-d H:i'),
            'user' => Auth::user()->name ?? 'System',
            'groups' => $groups
        ];
        
        $pdf = PDF::loadView('spending.groups.pdf', $data, [], [
            'mode' => 'utf-8', 'format' => 'A4', 'default_font' => 'nrt', 
            'orientation' => 'P', 'margin_header' => 10, 'margin_footer' => 10,
        ]);
        
        return $pdf->stream('group_spending_report.pdf');
    }
}