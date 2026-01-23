<?php

namespace App\Http\Controllers;

use App\Models\CashBox;
use App\Models\Branch;
use App\Models\CurrencyConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use PDF; // Correct Facade Import

class CashBoxController extends Controller
{
    public function index()
    {
        // Increased pagination to 50 for better "Spreadsheet" feel
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
        ]);

        foreach ($request->boxes as $data) {
            // 1. Determine if Active (Checkboxes are not sent if unchecked)
            $isActive = isset($data['is_active']);

            if (isset($data['id']) && $data['id']) {
                // UPDATE EXISTING
                $box = CashBox::find($data['id']);
                if ($box) {
                    $box->update([
                        'name' => $data['name'],
                        'type' => $data['type'] ?? null,
                        'currency_id' => $data['currency_id'],
                        'branch_id' => $data['branch_id'],
                        'balance' => $data['balance'],
                        'is_active' => $isActive,
                    ]);
                }
            } else {
                // CREATE NEW
                CashBox::create([
                    'name' => $data['name'],
                    'type' => $data['type'] ?? null,
                    'currency_id' => $data['currency_id'],
                    'branch_id' => $data['branch_id'],
                    'balance' => $data['balance'],
                    'date_opened' => now(),
                    'user_id' => Auth::id(),
                    'is_active' => $isActive,
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
            'name' => $request->name,
            'type' => $request->type,
            'currency_id' => $request->currency_id,
            'branch_id' => $request->branch_id,
            'balance' => $request->balance,
            'description' => $request->description,
            'date_opened' => now(),
            'user_id' => Auth::id(),
        ]);

        return back()->with('success', __('cash_box.created'));
    }

 public function destroy($id)
    {
        $cashBox = CashBox::find($id);

        if ($cashBox) {
            // 1. Save WHO is deleting the record
            $cashBox->update(['deleted_by' => Auth::id()]);

            // 2. Perform the Soft Delete
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
            'name' => $request->name,
            'type' => $request->type,
            'currency_id' => $request->currency_id,
            'branch_id' => $request->branch_id,
            'balance' => $request->balance,
            'description' => $request->description,
            'is_active' => $request->has('is_active'),
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
            fputcsv($file, ['ID', 'Name', 'Type', 'Currency', 'Balance', 'Branch', 'Status']);

            $items = CashBox::with(['currency', 'branch'])->get();
            foreach ($items as $item) {
                fputcsv($file, [
                    $item->id,
                    $item->name,
                    $item->type,
                    $item->currency->currency_type ?? 'N/A',
                    $item->balance,
                    $item->branch->name ?? 'N/A',
                    $item->is_active ? 'Active' : 'Inactive'
                ]);
            }
            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }

    /**
     * Download PDF Report
     */
    public function downloadPdf()
    {
        // 1. Fetch Data
        $cashBoxes = CashBox::with(['currency', 'branch', 'user'])->get();

        // 2. Prepare Data Array
        $data = [
            'title' => 'راپۆرتی سندوقەکان', // "Cash Box Report" in Kurdish
            'date' => date('Y-m-d H:i'),
            'user' => Auth::user()->name,
            'cashBoxes' => $cashBoxes
        ];

        // 3. Load PDF View
        $pdf = PDF::loadView('cash_boxes.pdf', $data, [], [
            'mode' => 'utf-8',
            'format' => 'A4',
            'default_font' => 'nrt', // MUST match the key in config/pdf.php
            'margin_header' => 10,
            'margin_footer' => 10,
            'orientation' => 'P', // Portrait
        ]);

        // 4. Stream the file (Open in browser)
        return $pdf->stream('cash_box_report.pdf');
    }
}