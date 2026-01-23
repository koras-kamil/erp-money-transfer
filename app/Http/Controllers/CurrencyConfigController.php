<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CurrencyConfig;
use App\Models\Branch; // <--- ADDED THIS
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
        // Fetch branches for the dropdown
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
                    // Save branch_id from the dropdown
                    'branch_id' => $data['branch_id'] ?? null,
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
                        // Add creator ID for new records
                        $saveData['created_by'] = Auth::id();
                        CurrencyConfig::create($saveData);
                    }
                }
            }
        });

        return back()->with('success', __('currency.saved') ?? 'Saved Successfully');
    }

    /**
     * Soft Delete (Move to Trash)
     */
public function destroy($id)
    {
        $currency = CurrencyConfig::find($id);
        
        if ($currency) {
            // 1. Save the ID of the logged-in user who is deleting this
            $currency->deleted_by = Auth::id();
            $currency->save(); // Save the change to the database
            
            // 2. Now perform the Soft Delete
            $currency->delete();
            
            return back()->with('success', 'Moved to trash successfully');
        }

        return back()->with('error', 'Item not found');
    }

    /**
     * View Trash Page
     */
    public function trash()
    {
        $currencies = CurrencyConfig::onlyTrashed()->orderBy('deleted_at', 'desc')->get();
        return view('currency.trash', compact('currencies'));
    }

    /**
     * Restore from Trash
     */
    public function restore($id)
    {
        $currency = CurrencyConfig::onlyTrashed()->findOrFail($id);
        $currency->restore();
        return back()->with('success', __('currency.restored'));
    }

    /**
     * Permanently Delete (Force Delete)
     */
    public function forceDelete($id)
    {
        try {
            $currency = CurrencyConfig::onlyTrashed()->findOrFail($id);
            $currency->forceDelete();
            
            return back()->with('success', __('currency.permanently_deleted'));

        } catch (QueryException $e) {
            // Check for Postgres/MySQL Foreign Key Violation (23503)
            if ($e->getCode() == "23503") {
                return back()->with('error', 
                    app()->getLocale() == 'ku' 
                    ? 'ناتوانی ئەم دراوە بسڕیتەوە چونکە بەکارهاتووە لە سندوقەکاندا!' 
                    : 'Cannot delete this currency because it is linked to records in Cash Boxes!'
                );
            }

            return back()->with('error', __('currency.error') ?? 'An unexpected error occurred');
        }
    }

    /**
     * Generate PDF Report (mPDF)
     */
  public function downloadPdf()
    {
        // Fetch all currencies
        $currencies = CurrencyConfig::all();

        $data = [
            'title'      => 'لیستی دراوەکان',
            'date'       => date('Y-m-d H:i'),
            'user'       => Auth::user()->name ?? 'System',
            'currencies' => $currencies // <--- FIXED: Key matches '$currencies' in your view
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