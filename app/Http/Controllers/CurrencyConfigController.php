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
     * Handle Bulk Save (Create & Update)
     */
    public function store(Request $request)
    {
        DB::transaction(function () use ($request) {
            $inputs = $request->input('currencies', []);

            foreach ($inputs as $data) {
                // Prepare data array safely
                $saveData = [
                    'currency_type' => $data['currency_type'],
                    'symbol'        => $data['symbol'],
                    'digit_number'  => $data['digit_number'],
                    'price_total'   => $data['price_total'],
                    'price_single'  => $data['price_single'],
                    'price_sell'    => $data['price_sell'] ?? 0,
                    'branch_id'     => $data['branch_id'] ?? null,
                    'is_active'     => isset($data['is_active']) ? 1 : 0,
                ];

                if (isset($data['id'])) {
                    // Update existing
                    $currency = CurrencyConfig::find($data['id']);
                    if ($currency) {
                        $currency->update($saveData);
                    }
                } else {
                    // Create new (only if type is provided)
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
     * Soft Delete (Move to Trash) - Single Item
     */
    public function destroy($id)
    {
        $currency = CurrencyConfig::find($id);
        
        if ($currency) {
            // 1. Save the ID of the logged-in user who is deleting this
            $currency->update(['deleted_by' => Auth::id()]);
            
            // 2. Now perform the Soft Delete
            $currency->delete();
            
            return back()->with('success', __('currency.deleted') ?? 'Moved to trash successfully');
        }

        return back()->with('error', __('currency.not_found') ?? 'Item not found');
    }

    /**
     * Soft Delete (Move to Trash) - BULK ITEMS
     */
    public function bulkDelete(Request $request)
    {
        $ids = json_decode($request->input('ids', '[]'), true);

        if (!empty($ids) && is_array($ids)) {
            foreach($ids as $id) {
                $currency = CurrencyConfig::find($id);
                if($currency) {
                    // 1. Save who is deleting the item
                    $currency->update(['deleted_by' => Auth::id()]);
                    
                    // 2. Perform delete
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
        // Load the 'deleter' relationship (User model) to show names in the trash view
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
     * Permanently Delete (Force Delete) - Single Item
     */
    public function forceDelete($id)
    {
        try {
            $currency = CurrencyConfig::onlyTrashed()->findOrFail($id);
            $currency->forceDelete();
            
            return back()->with('success', __('currency.permanently_deleted'));

        } catch (QueryException $e) {
            // Check for Foreign Key Violation
            if ($e->getCode() == "23503") {
                return back()->with('error', __('currency.cannot_delete_used') ?? 'Cannot delete: Item is in use.');
            }
            return back()->with('error', __('currency.error') ?? 'An unexpected error occurred');
        }
    }

    /**
     * Permanently Delete (Force Delete) - BULK ITEMS
     */
    public function bulkForceDelete(Request $request)
    {
        $ids = json_decode($request->input('ids', '[]'), true);

        if (!empty($ids) && is_array($ids)) {
            try {
                // We must use loop here to catch errors for individual items if needed, 
                // or we can try delete all and catch generic error.
                // Using whereIn->forceDelete is faster but fails if ONE item has constraint.
                
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