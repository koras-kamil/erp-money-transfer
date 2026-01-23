<?php

namespace App\Http\Controllers;

use App\Models\GroupSpending;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Mccarlosen\LaravelMpdf\Facades\LaravelMpdf as PDF;

class GroupSpendingController extends Controller
{
    /**
     * Display the main editing list.
     */
    public function index()
    {
        // Using get() here assuming you want a full list for the edit sheet.
        $groups = GroupSpending::with(['branch', 'creator'])->orderBy('id')->get();
        $branches = Branch::all();
        
        return view('spending.groups.index', compact('groups', 'branches'));
    }

    /**
     * Display the Trash page.
     */
    public function trash()
    {
        // Fixed: Uses paginate(10) instead of get() so ->links() works in the view.
        $groups = GroupSpending::onlyTrashed()
            ->with(['branch', 'deleter']) // Eager load relationships
            ->orderBy('deleted_at', 'desc')
            ->paginate(10); 

        // Ensure your blade file is at resources/views/spending/groups/trash.blade.php
        return view('spending.groups.trash', compact('groups'));
    }

    /**
     * Store or Update Spendings (Bulk Action).
     */
    public function store(Request $request)
    {
        $request->validate(['spendings' => 'array']);

        DB::transaction(function () use ($request) {
            $inputs = $request->input('spendings', []);
            $lastId = GroupSpending::max('id') ?? 0;

            foreach ($inputs as $data) {
                $saveData = [
                    'name'            => $data['name'],
                    'accountant_code' => $data['accountant_code'] ?? null,
                    'branch_id'       => $data['branch_id'] ?? null,
                ];

                if (isset($data['id']) && $data['id']) {
                    // Update Existing
                    $group = GroupSpending::find($data['id']);
                    if ($group) {
                        $group->update($saveData);
                    }
                } else {
                    // Create New
                    if (!empty($data['name'])) {
                        $lastId++;
                        // Auto-generate Code: GS-0001
                        $saveData['code'] = 'GS-' . str_pad($lastId, 4, '0', STR_PAD_LEFT);
                        $saveData['created_by'] = Auth::id();
                        GroupSpending::create($saveData);
                    }
                }
            }
        });

        return back()->with('success', __('spending.saved'));
    }

    /**
     * Soft Delete a Group Spending.
     */
    public function destroy($id)
    {
        $group = GroupSpending::find($id);
        
        if ($group) {
            // 1. Save who deleted it
            $group->update(['deleted_by' => Auth::id()]); 
            
            // 2. Soft Delete
            $group->delete(); 
            
            return back()->with('success', __('spending.deleted'));
        }
        
        return back()->with('error', __('spending.not_found'));
    }

    /**
     * Restore from Trash.
     */
    public function restore($id)
    {
        $group = GroupSpending::withTrashed()->find($id);
        
        if ($group) {
            $group->restore();
            return back()->with('success', __('spending.restored'));
        }

        return back()->with('error', __('spending.not_found'));
    }

    /**
     * Permanently Delete.
     */
    public function forceDelete($id)
    {
        $group = GroupSpending::withTrashed()->find($id);

        if ($group) {
            $group->forceDelete();
            return back()->with('success', __('spending.permanently_deleted'));
        }

        return back()->with('error', __('spending.not_found'));
    }

    /**
     * Generate PDF Report.
     */
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
            'mode' => 'utf-8',
            'format' => 'A4',
            'default_font' => 'nrt', 
            'orientation' => 'P',
            'margin_header' => 10,
            'margin_footer' => 10,
        ]);
        
        return $pdf->stream('group_spending_report.pdf');
    }
}