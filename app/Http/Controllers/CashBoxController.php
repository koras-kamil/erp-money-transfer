<?php

namespace App\Http\Controllers;

use App\Models\CashBox;
use App\Models\CashBoxBalance; // 🟢 NEW MODEL IMPORTED
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
        $branches = Branch::where('is_active', true)->get();
        $currencies = CurrencyConfig::where('is_active', true)->get();
        
        // Fetch Cash Boxes with their new dynamic balances
        $cashBoxesRaw = CashBox::with(['balances.currency', 'branch', 'user'])->latest()->get();

        // Format the data perfectly for AlpineJS to build dynamic columns
        $cashBoxes = $cashBoxesRaw->map(function($box) use ($currencies) {
            $balances = [];
            // Initialize all active currencies to 0
            foreach($currencies as $curr) {
                $balances['curr_' . $curr->id] = 0;
            }
            // Override with actual balances from database
            foreach($box->balances as $b) {
                $balances['curr_' . $b->currency_id] = floatval($b->balance);
            }

            return [
                'id'          => $box->id,
                'code'        => $box->code ?? '-',
                'name'        => $box->name,
                'branch_id'   => $box->branch_id,
                'description' => $box->description,
                'is_active'   => $box->is_active,
                'user_id'     => $box->user_id,
                'created_at'  => $box->created_at,
                'balances'    => $balances // 🟢 Dynamic balances payload
            ];
        });

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
            'boxes.*.branch_id' => 'nullable|exists:branches,id',
            'boxes.*.description' => 'nullable|string|max:1000',
            'boxes.*.is_active' => 'nullable',
            // 🟢 Removed balance & currency_id validation
        ]);

        foreach ($request->boxes as $data) {
            $isActive = filter_var($data['is_active'] ?? false, FILTER_VALIDATE_BOOLEAN);

            if (!empty($data['id']) && is_numeric($data['id'])) {
                // --- UPDATE EXISTING ---
                $box = CashBox::find($data['id']);
                if ($box) {
                    $box->update([
                        'name'        => $data['name'],
                        'branch_id'   => $data['branch_id'] ?? null,
                        'description' => $data['description'] ?? null,
                        'is_active'   => $isActive,
                    ]);
                }
            } else {
                // --- CREATE NEW ---
                $box = CashBox::create([
                    'name'        => $data['name'],
                    'branch_id'   => $data['branch_id'] ?? null,
                    'description' => $data['description'] ?? null,
                    'date_opened' => now(),
                    'user_id'     => Auth::id(),
                    'is_active'   => $isActive,
                ]);
            }

            // 🟢 SYNC MULTI-CURRENCY BALANCES
            if (isset($data['balances']) && is_array($data['balances'])) {
                foreach ($data['balances'] as $currId => $amount) {
                    CashBoxBalance::updateOrCreate(
                        ['cash_box_id' => $box->id, 'currency_id' => $currId],
                        ['balance' => $amount ?? 0]
                    );
                }
            }
        }

        return back()->with('success', __('cash_box.saved_successfully'));
    }

    public function store(Request $request)
    {
        // 🟢 Check if it's the AlpineJS single row submission format
        if ($request->has('types') && is_array($request->types)) {
            $data = $request->types[0];
            
            $box = CashBox::updateOrCreate(
                ['id' => (!empty($data['id']) && is_numeric($data['id'])) ? $data['id'] : null],
                [
                    'name'        => $data['name'],
                    'branch_id'   => $data['branch_id'] ?? null,
                    'description' => $data['description'] ?? null,
                    'is_active'   => filter_var($data['is_active'] ?? true, FILTER_VALIDATE_BOOLEAN),
                    'user_id'     => Auth::id(),
                ]
            );

            // Sync Balances
            if (isset($data['balances']) && is_array($data['balances'])) {
                foreach ($data['balances'] as $currency_id => $amount) {
                    CashBoxBalance::updateOrCreate(
                        ['cash_box_id' => $box->id, 'currency_id' => $currency_id],
                        ['balance' => $amount ?? 0]
                    );
                }
            }

            return back()->with('success', __('cash_box.saved_successfully'));
        }

        // --- Standard Form Fallback ---
        $request->validate([
            'name' => 'required|string|max:255',
            'balances' => 'array',
        ]);

        $box = CashBox::create([
            'name'        => $request->name,
            'branch_id'   => $request->branch_id,
            'description' => $request->description,
            'date_opened' => now(),
            'user_id'     => Auth::id(),
            'is_active'   => true,
        ]);

        if ($request->has('balances')) {
            foreach ($request->balances as $currency_id => $amount) {
                CashBoxBalance::create([
                    'cash_box_id' => $box->id,
                    'currency_id' => $currency_id,
                    'balance'     => $amount ?? 0,
                ]);
            }
        }

        return back()->with('success', __('cash_box.created'));
    }

    public function destroy($id)
    {
        $cashBox = CashBox::find($id);

        if ($cashBox) {
            $cashBox->update(['deleted_by' => Auth::id()]);
            $cashBox->delete(); // CashBoxBalance rows will auto-delete if DB has cascading setup
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
            'balances' => 'array',
        ]);

        $cashBox->update([
            'name'        => $request->name,
            'branch_id'   => $request->branch_id,
            'description' => $request->description,
            'is_active'   => $request->has('is_active'),
        ]);

        if ($request->has('balances')) {
            foreach ($request->balances as $currency_id => $amount) {
                CashBoxBalance::updateOrCreate(
                    ['cash_box_id' => $cashBox->id, 'currency_id' => $currency_id],
                    ['balance' => $amount ?? 0]
                );
            }
        }

        return redirect()->route('cash-boxes.index')->with('success', __('cash_box.updated'));
    }

    public function trash()
    {
        // 🟢 Replaced 'currency' with 'balances'
        $cashBoxes = CashBox::onlyTrashed()->with(['balances.currency', 'branch', 'user'])->latest()->paginate(20);
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
            
            // 🟢 Build Headers dynamically based on active currencies
            $currencies = CurrencyConfig::where('is_active', true)->get();
            $csvHeaders = ['ID', 'Name', 'Branch', 'Description', 'Status'];
            foreach($currencies as $curr) {
                $csvHeaders[] = 'Balance (' . $curr->currency_type . ')';
            }
            fputcsv($file, $csvHeaders);

            $items = CashBox::with(['balances', 'branch'])->get();
            foreach ($items as $item) {
                $row = [
                    $item->id,
                    $item->name,
                    $item->branch->name ?? 'N/A',
                    $item->description,
                    $item->is_active ? 'Active' : 'Inactive'
                ];
                
                // Add balance columns for each currency
                foreach($currencies as $curr) {
                    $balance = $item->balances->where('currency_id', $curr->id)->first();
                    $row[] = $balance ? floatval($balance->balance) : 0;
                }
                
                fputcsv($file, $row);
            }
            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }

    public function downloadPdf()
    {
        // 🟢 Replaced 'currency' with 'balances.currency'
        $cashBoxes = CashBox::with(['balances.currency', 'branch', 'user'])->get();
        $currencies = CurrencyConfig::where('is_active', true)->get();

        $data = [
            'title'      => 'راپۆرتی سندوقەکان', 
            'date'       => date('Y-m-d H:i'),
            'user'       => Auth::user()->name,
            'cashBoxes'  => $cashBoxes,
            'currencies' => $currencies // Pass currencies to PDF to build headers
        ];

        $pdf = PDF::loadView('cash_boxes.pdf', $data, [], [
            'mode'           => 'utf-8',
            'format'         => 'A4',
            'default_font'   => 'nrt', 
            'margin_header'  => 10,
            'margin_footer'  => 10,
            'orientation'    => 'L', // Switched to Landscape to fit multiple currencies
        ]);

        return $pdf->stream('cash_box_report.pdf');
    }

    public function bulkDelete(Request $request)
    {
        $ids = json_decode($request->input('ids', '[]'), true);

        if (!empty($ids) && is_array($ids)) {
            foreach($ids as $id) {
                $cashBox = CashBox::find($id);
                if($cashBox) {
                    $cashBox->update(['deleted_by' => Auth::id()]);
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
                if ($e->getCode() == "23503") {
                    return back()->with('error', __('cash_box.cannot_delete_used_bulk'));
                }
                return back()->with('error', __('cash_box.error'));
            }
        }

        return back()->with('error', __('cash_box.nothing_selected'));
    }
}