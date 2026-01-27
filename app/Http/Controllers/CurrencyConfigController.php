<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CurrencyConfig;
use App\Models\Branch; 
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Mccarlosen\LaravelMpdf\Facades\LaravelMpdf as PDF;

class CurrencyConfigController extends Controller
{
    /**
     * Display the main table (Active currencies)
     */
    public function index()
    {
        $currencies = CurrencyConfig::orderBy('id')->get();
        $branches = Branch::all(); 

        return view('currency.index', compact('currencies', 'branches'));
    }

    /**
     * Handle Bulk Save (Create & Update) with SMART OPERATOR LOGIC
     */
    public function store(Request $request)
    {
        DB::transaction(function () use ($request) {
            $inputs = $request->input('currencies', []);

            foreach ($inputs as $data) {
                
                // 1. Clean the Price (Remove commas, e.g. "1,500" -> "1500")
                $priceTotal = isset($data['price_total']) ? str_replace(',', '', $data['price_total']) : 0;
                
                // 2. Calculate Single Price (Price for $1)
                $priceSingle = $priceTotal > 0 ? ($priceTotal / 100) : 0;

                // 3. SMART AUTO-DETECT OPERATOR
                // If the user selected one manually, keep it. Otherwise, auto-detect.
                if (!empty($data['math_operator'])) {
                    $operator = $data['math_operator'];
                } 
                else {
                    // LOGIC:
                    // Rate > 2.0 (e.g. 1500 IQD) -> DIVIDE (/)
                    // Rate <= 2.0 (e.g. 0.95 EUR) -> MULTIPLY (*)
                    if ($priceSingle > 2.0) {
                        $operator = '/';
                    } else {
                        $operator = '*';
                    }
                }

                $saveData = [
                    'currency_type' => $data['currency_type'],
                    'symbol'        => $data['symbol'],
                    'digit_number'  => $data['digit_number'],
                    'price_total'   => $priceTotal,
                    'price_single'  => $priceSingle,
                    'price_sell'    => $data['price_sell'] ?? 0,
                    'branch_id'     => $data['branch_id'] ?? null,
                    'is_active'     => isset($data['is_active']) ? 1 : 0,
                    'math_operator' => $operator, // <--- SAVING THE SMART OPERATOR
                ];

                if (isset($data['id'])) {
                    // Update existing
                    $currency = CurrencyConfig::find($data['id']);
                    if ($currency) {
                        $currency->update($saveData);
                    }
                } else {
                    // Create new
                    if (!empty($data['currency_type'])) {
                        $saveData['created_by'] = Auth::id();
                        CurrencyConfig::create($saveData);
                    }
                }
            }
        });

        return back()->with('success', __('currency.saved') ?? 'Saved Successfully');
    }

    /**
     * Update Rates (Bulk update from Modal) - ALSO UPDATES OPERATOR
     */
    public function updateRates(Request $request)
    {
        $request->validate([
            'rates' => 'required|array',
        ]);

        foreach ($request->rates as $id => $priceTotalInput) {
            // 1. Clean Data
            $priceTotal = str_replace(',', '', $priceTotalInput);
            $priceSingle = $priceTotal / 100;

            // 2. RE-EVALUATE OPERATOR
            // If market changes significantly (e.g., currency revaluation), update operator.
            if ($priceSingle > 2.0) {
                $op = '/';
            } else {
                $op = '*';
            }

            // 3. Update Record
            CurrencyConfig::where('id', $id)->update([
                'price_total'   => $priceTotal,
                'price_single'  => $priceSingle,
                'math_operator' => $op 
            ]);
        }

        return back()->with('success', __('messages.rates_updated_success') ?? 'Rates updated successfully');
    }

    /**
     * Soft Delete (Move to Trash) - Single Item
     */
    public function destroy($id)
    {
        $currency = CurrencyConfig::find($id);
        
        if ($currency) {
            $currency->update(['deleted_by' => Auth::id()]);
            $currency->delete();
            return back()->with('success', __('currency.deleted') ?? 'Moved to trash successfully');
        }

        return back()->with('error', __('currency.not_found') ?? 'Item not found');
    }

    /**
     * Soft Delete - BULK ITEMS
     */
    public function bulkDelete(Request $request)
    {
        $ids = json_decode($request->input('ids', '[]'), true);

        if (!empty($ids) && is_array($ids)) {
            foreach($ids as $id) {
                $currency = CurrencyConfig::find($id);
                if($currency) {
                    $currency->update(['deleted_by' => Auth::id()]);
                    $currency->delete();
                }
            }
            return back()->with('success', __('currency.deleted_selected'));
        }

        return back()->with('error', __('currency.nothing_selected'));
    }

    /**
     * View Trash Page
     */
    public function trash()
    {
        $currencies = CurrencyConfig::onlyTrashed()
            ->with('deleter') 
            ->orderBy('deleted_at', 'desc')
            ->get();
            
        return view('currency.trash', compact('currencies'));
    }

    /**
     * Restore from Trash - Single Item
     */
    public function restore($id)
    {
        $currency = CurrencyConfig::onlyTrashed()->findOrFail($id);
        $currency->restore();
        return back()->with('success', __('currency.restored'));
    }

    /**
     * Restore from Trash - BULK ITEMS
     */
    public function bulkRestore(Request $request)
    {
        $ids = json_decode($request->input('ids', '[]'), true);

        if (!empty($ids) && is_array($ids)) {
            CurrencyConfig::onlyTrashed()->whereIn('id', $ids)->restore();
            return back()->with('success', __('currency.restored_selected'));
        }

        return back()->with('error', __('currency.nothing_selected'));
    }

    /**
     * Permanently Delete - Single Item
     */
    public function forceDelete($id)
    {
        try {
            $currency = CurrencyConfig::onlyTrashed()->findOrFail($id);
            $currency->forceDelete();
            return back()->with('success', __('currency.permanently_deleted'));

        } catch (QueryException $e) {
            if ($e->getCode() == "23503") {
                return back()->with('error', __('currency.cannot_delete_used') ?? 'Cannot delete: Item is in use.');
            }
            return back()->with('error', __('currency.error') ?? 'An unexpected error occurred');
        }
    }

    /**
     * Permanently Delete - BULK ITEMS
     */
    public function bulkForceDelete(Request $request)
    {
        $ids = json_decode($request->input('ids', '[]'), true);

        if (!empty($ids) && is_array($ids)) {
            try {
                $items = CurrencyConfig::onlyTrashed()->whereIn('id', $ids)->get();
                foreach($items as $item) {
                    $item->forceDelete();
                }
                return back()->with('success', __('currency.permanently_deleted_selected'));

            } catch (QueryException $e) {
                if ($e->getCode() == "23503") {
                    return back()->with('error', __('currency.cannot_delete_used_bulk') ?? 'Some items could not be deleted because they are in use.');
                }
                return back()->with('error', __('currency.error'));
            }
        }

        return back()->with('error', __('currency.nothing_selected'));
    }

    /**
     * Generate PDF Report (mPDF)
     */
    public function downloadPdf()
    {
        $currencies = CurrencyConfig::all();

        $data = [
            'title'      => __('currency.config_title'),
            'date'       => date('Y-m-d H:i'),
            'user'       => Auth::user()->name ?? 'System',
            'currencies' => $currencies
        ];

        $pdf = PDF::loadView('currency.pdf', $data, [], [
            'mode' => 'utf-8',
            'format' => 'A4',
            'default_font' => 'nrt', 
            'margin_header' => 10,
            'margin_footer' => 10,
            'orientation' => 'P',
        ]);

        return $pdf->stream('currency_report.pdf');
    }
}