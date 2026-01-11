<?php

namespace App\Http\Controllers;
use App\Models\CashBox;
use App\Models\Branch;
use App\Models\CurrencyConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response; 
// <--- Add this at top


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
        $cashBox->delete();
        return back()->with('success', __('cash_box.deleted'));
    }

    public function edit(CashBox $cashBox)
    {
        $branches = Branch::all();
        $currencies = CurrencyConfig::where('is_active', true)->get();
        
        return view('cash_boxes.edit', compact('cashBox', 'branches', 'currencies'));
    }

    // 2. SAVE THE CHANGES
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
            // We use 'has' to check checkboxes. If checked = true, if not = false.
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('cash-boxes.index')->with('success', __('cash_box.updated'));
    }


    public function trash()
    {
        $cashBoxes = CashBox::onlyTrashed()->with(['currency', 'branch', 'user'])->latest()->paginate(10);
        return view('cash_boxes.trash', compact('cashBoxes'));
    }

    // 2. RESTORE (Bring back from trash)
    public function restore($id)
    {
        CashBox::onlyTrashed()->where('id', $id)->restore();
        return back()->with('success', __('cash_box.restored'));
    }

    // 3. FORCE DELETE (Delete forever)
    public function forceDelete($id)
    {
        CashBox::onlyTrashed()->where('id', $id)->forceDelete();
        return back()->with('success', __('cash_box.permanently_deleted'));
    }

    // 4. EXPORT TO EXCEL (CSV)
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

    // 2. ADD THIS NEW FUNCTION
    public function downloadPdf()
    {
        // Get all cash boxes (you can also use filters here if you want)
        $cashBoxes = CashBox::with(['currency', 'branch', 'user'])->get();

        // Load the specific PDF view (we will create this in Step 4)
        $pdf = Pdf::loadView('cash_boxes.pdf', compact('cashBoxes'));

        // Set Paper Size to A4
        $pdf->setPaper('a4', 'portrait'); // or 'landscape'

        // Download the file
        return $pdf->download('cash_box_report.pdf');
    }
}