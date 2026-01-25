<?php

namespace App\Http\Controllers;

use App\Models\CashBox;
use App\Models\Branch;
use App\Models\CurrencyConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use PDF; 
use Illuminate\Database\QueryException;

class CashBoxController extends Controller
{
    public function index()
    {
        $cashBoxes = CashBox::with(['currency', 'branch', 'user'])->latest()->paginate(50);
        $branches = Branch::where('is_active', true)->get();
        $currencies = CurrencyConfig::where('is_active', true)->get();

        return view('cash_boxes.index', compact('cashBoxes', 'branches', 'currencies'));
    }

    /**
     * Handle Bulk Save (New Rows + Updates) from the Grid View
     */
    public function storeBulk(Request $request)
    {
        $request->validate([
            'boxes' => 'required|array',
            'boxes.*.name' => 'required|string|max:255',
            'boxes.*.currency_id' => 'required|exists:currency_configs,id',
            'boxes.*.branch_id' => 'required|exists:branches,id',
            'boxes.*.balance' => 'required|numeric',
            'boxes.*.description' => 'nullable|string|max:1000', // Added validation for description
        ]);

        foreach ($request->boxes as $data) {
            $isActive = isset($data['is_active']);

            if (isset($data['id']) && $data['id']) {
                // UPDATE EXISTING
                $box = CashBox::find($data['id']);
                if ($box) {
                    $box->update([
                        'name'        => $data['name'],
                        'type'        => $data['type'] ?? null,
                        'currency_id' => $data['currency_id'],
                        'branch_id'   => $data['branch_id'],
                        'balance'     => $data['balance'],
                        'description' => $data['description'] ?? null, // ADDED THIS LINE
                        'is_active'   => $isActive,
                    ]);
                }
            } else {
                // CREATE NEW
                CashBox::create([
                    'name'        => $data['name'],
                    'type'        => $data['type'] ?? null,
                    'currency_id' => $data['currency_id'],
                    'branch_id'   => $data['branch_id'],
                    'balance'     => $data['balance'],
                    'description' => $data['description'] ?? null, // ADDED THIS LINE
                    'date_opened' => now(),
                    'user_id'     => Auth::id(),
                    'is_active'   => $isActive,
                ]);
            }
        }

        return back()->with('success', __('cash_box.saved_successfully'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'currency_id' => 'required|exists:currency_configs,id',
            'branch_id' => 'required|exists:branches,id',
            'balance' => 'required|numeric',
        ]);

        CashBox::create([
            'name'        => $request->name,
            'type'        => $request->type,
            'currency_id' => $request->currency_id,
            'branch_id'   => $request->branch_id,
            'balance'     => $request->balance,
            'description' => $request->description,
            'date_opened' => now(),
            'user_id'     => Auth::id(),
            'is_active'   => true,
        ]);

        return back()->with('success', __('cash_box.created'));
    }

    public function destroy($id)
    {
        $cashBox = CashBox::find($id);

        if ($cashBox) {
            $cashBox->update(['deleted_by' => Auth::id()]);
            $cashBox->delete();
            return back()->with('success', __('cash_box.deleted'));
        }

        return back()->with('error', __('cash_box.not_found'));
    }

    public function edit(CashBox $cashBox)
    {
        $branches = Branch::all();
        $currencies = CurrencyConfig::where('is_active', true)->get();
        return view('cash_boxes.edit', compact('cashBox', 'branches', 'currencies'));
    }

    public function update(Request $request, CashBox $cashBox)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'currency_id' => 'required',
            'branch_id' => 'required',
            'balance' => 'required|numeric',
        ]);

        $cashBox->update([
            'name'        => $request->name,
            'type'        => $request->type,
            'currency_id' => $request->currency_id,
            'branch_id'   => $request->branch_id,
            'balance'     => $request->balance,
            'description' => $request->description,
            'is_active'   => $request->has('is_active'),
        ]);

        return redirect()->route('cash-boxes.index')->with('success', __('cash_box.updated'));
    }

    public function trash()
    {
        $cashBoxes = CashBox::onlyTrashed()->with(['currency', 'branch', 'user'])->latest()->paginate(20);
        return view('cash_boxes.trash', compact('cashBoxes'));
    }

    public function restore($id)
    {
        $cashBox = CashBox::onlyTrashed()->findOrFail($id);
        $cashBox->restore();
        return back()->with('success', __('cash_box.restored'));
    }

    public function forceDelete($id)
    {
        $cashBox = CashBox::onlyTrashed()->findOrFail($id);
        $cashBox->forceDelete();
        return back()->with('success', __('cash_box.permanently_deleted'));
    }

    public function export()
    {
        $filename = "cash_boxes_" . date('Y-m-d') . ".csv";
        $headers = [
            "Content-Type" => "text/csv",
            "Content-Disposition" => "attachment; filename=\"$filename\"",
        ];

        $callback = function() {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Name', 'Type', 'Currency', 'Balance', 'Branch', 'Description', 'Status']);

            $items = CashBox::with(['currency', 'branch'])->get();
            foreach ($items as $item) {
                fputcsv($file, [
                    $item->id,
                    $item->name,
                    $item->type,
                    $item->currency->currency_type ?? 'N/A',
                    $item->balance,
                    $item->branch->name ?? 'N/A',
                    $item->description,
                    $item->is_active ? 'Active' : 'Inactive'
                ]);
            }
            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }

    public function downloadPdf()
    {
        $cashBoxes = CashBox::with(['currency', 'branch', 'user'])->get();

        $data = [
            'title'     => 'راپۆرتی سندوقەکان', 
            'date'      => date('Y-m-d H:i'),
            'user'      => Auth::user()->name,
            'cashBoxes' => $cashBoxes
        ];

        $pdf = PDF::loadView('cash_boxes.pdf', $data, [], [
            'mode'           => 'utf-8',
            'format'         => 'A4',
            'default_font'   => 'nrt', 
            'margin_header'  => 10,
            'margin_footer'  => 10,
            'orientation'    => 'P', 
        ]);

        return $pdf->stream('cash_box_report.pdf');
    }



public function bulkDelete(Request $request)
{
    $ids = json_decode($request->input('ids', '[]'), true);

    if (!empty($ids) && is_array($ids)) {
        // Loop through IDs to save 'deleted_by' for each item
        foreach($ids as $id) {
            $cashBox = CashBox::find($id);
            if($cashBox) {
                // 1. Record who is deleting it
                $cashBox->update(['deleted_by' => Auth::id()]);
                
                // 2. Perform the soft delete
                $cashBox->delete();
            }
        }
        
        return back()->with('success', __('cash_box.deleted_selected'));
    }

    return back()->with('error', __('cash_box.nothing_selected'));
}

public function bulkRestore(Request $request)
{
    $ids = json_decode($request->input('ids', '[]'), true);

    if (!empty($ids) && is_array($ids)) {
        CashBox::onlyTrashed()->whereIn('id', $ids)->restore();
        return back()->with('success', __('cash_box.restored_selected'));
    }

    return back()->with('error', __('cash_box.nothing_selected'));
}

public function bulkForceDelete(Request $request)
{
    $ids = json_decode($request->input('ids', '[]'), true);

    if (!empty($ids) && is_array($ids)) {
        try {
            $items = CashBox::onlyTrashed()->whereIn('id', $ids)->get();
            foreach($items as $item) {
                $item->forceDelete();
            }
            return back()->with('success', __('cash_box.permanently_deleted_selected'));
        } catch (QueryException $e) {
             // Foreign Key Constraint Error (Postgres/MySQL)
            if ($e->getCode() == "23503") {
                return back()->with('error', __('cash_box.cannot_delete_used_bulk'));
            }
            return back()->with('error', __('cash_box.error'));
        }
    }

    return back()->with('error', __('cash_box.nothing_selected'));
}


}