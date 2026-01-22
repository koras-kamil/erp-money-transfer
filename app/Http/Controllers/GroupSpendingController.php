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
    public function index()
    {
        $groups = GroupSpending::with(['branch', 'creator'])->orderBy('id')->get();
        $branches = Branch::all();
        
        return view('spending.groups.index', compact('groups', 'branches'));
    }

    public function trash()
    {
        $groups = GroupSpending::onlyTrashed()->with(['branch', 'creator'])->latest()->get();
        return view('spending.groups.trash', compact('groups'));
    }

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
                    // Update
                    $group = GroupSpending::find($data['id']);
                    if ($group) $group->update($saveData);
                } else {
                    // Create New
                    if (!empty($data['name'])) {
                        $lastId++;
                        $saveData['code'] = 'GS-' . str_pad($lastId, 4, '0', STR_PAD_LEFT);
                        $saveData['created_by'] = Auth::id();
                        GroupSpending::create($saveData);
                    }
                }
            }
        });

        return back()->with('success', __('spending.saved'));
    }

    public function destroy($id)
    {
        GroupSpending::findOrFail($id)->delete();
        return back()->with('success', __('spending.deleted'));
    }

    public function restore($id)
    {
        GroupSpending::withTrashed()->findOrFail($id)->restore();
        return back()->with('success', __('spending.restored'));
    }

    public function forceDelete($id)
    {
        GroupSpending::withTrashed()->findOrFail($id)->forceDelete();
        return back()->with('success', __('spending.permanently_deleted'));
    }

    /**
     * GENERATE PDF
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
        
        // IMPORTANT: Path must be resources/views/spending/groups/pdf.blade.php
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