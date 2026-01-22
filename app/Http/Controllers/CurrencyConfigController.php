<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CurrencyConfig;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use PDF; // Ensure 'PDF' alias is set in config/app.php or use the full class path

class CurrencyConfigController extends Controller
{
    /**
     * Display the main table (Active currencies)
     */
    public function index()
    {
        $currencies = CurrencyConfig::orderBy('id')->get();
        return view('currency.index', compact('currencies'));
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
                    'price_sell'    => $data['price_sell'] ?? 0, // Added based on your Model update
                    'branch'        => $data['branch'],
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
        
        if (!$currency) {
            return back()->with('error', __('currency.not_found'));
        }

        $currency->delete();
        return back()->with('success', __('currency.deleted'));
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
        $currencies = CurrencyConfig::all();

        $data = [
            'title' => 'لیستی دراوەکان', // Currency List
            'date' => date('Y-m-d H:i'),
            'user' => Auth::user()->name ?? 'System',
            'currencies' => $currencies
        ];

        $pdf = PDF::loadView('currency.pdf', $data, [], [
            'mode' => 'utf-8',
            'format' => 'A4',
            'default_font' => 'nrt', // Ensure this matches your config/pdf.php
            'margin_header' => 10,
            'margin_footer' => 10,
            'orientation' => 'P',
        ]);

        return $pdf->stream('currency_report.pdf');
    }
}