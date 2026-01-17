<?php

namespace App\Http\Controllers;

use App\Models\CashBox;
use App\Models\Branch;
use App\Models\CurrencyConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Barryvdh\DomPDF\Facade\Pdf; // Ensure you have this for PDF


class CashBoxController extends Controller
{
    public function index()
    {
        $cashBoxes = CashBox::with(['currency', 'branch', 'user'])->latest()->paginate(10);
        $branches = Branch::all();
        $currencies = CurrencyConfig::where('is_active', true)->get();

        return view('cash_boxes.index', compact('cashBoxes', 'branches', 'currencies'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'currency_id' => 'required|exists:currency_configs,id',
            'branch_id' => 'required|exists:branches,id',
            'balance' => 'required|numeric',
        ]);

        // ✅ Logged: create() triggers the 'created' event
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

    public function destroy(CashBox $cashBox)
    {
        // ✅ Logged: delete() on instance triggers 'deleted' event
        $cashBox->delete();
        return back()->with('success', __('cash_box.deleted'));
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

        // ✅ Logged: update() on instance triggers 'updated' event
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
        $cashBoxes = CashBox::onlyTrashed()->with(['currency', 'branch', 'user'])->latest()->paginate(10);
        return view('cash_boxes.trash', compact('cashBoxes'));
    }

    public function restore($id)
    {
        // ✅ FIX: Find the model instance first to trigger the log
        $cashBox = CashBox::onlyTrashed()->findOrFail($id);
        $cashBox->restore();
        
        return back()->with('success', __('cash_box.restored'));
    }

    public function forceDelete($id)
    {
        // ✅ FIX: Find the model instance first to trigger the log
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

            $items = CashBox::all();
            foreach ($items as $item) {
                fputcsv($file, [
                    $item->id,
                    $item->name,
                    $item->type,
                    $item->currency->code ?? 'N/A',
                    $item->balance,
                    $item->branch->name ?? 'N/A',
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
        $pdf = Pdf::loadView('cash_boxes.pdf', compact('cashBoxes'));
        $pdf->setPaper('a4', 'portrait');
        return $pdf->download('cash_box_report.pdf');
    }
}