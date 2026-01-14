<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CurrencyConfig;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;

class CurrencyConfigController extends Controller
{
    public function index()
    {
        $currencies = CurrencyConfig::orderBy('id')->get();
        return view('currency.index', compact('currencies'));
    }

    public function store(Request $request)
    {
        DB::transaction(function () use ($request) {
            $inputs = $request->input('currencies', []);

            foreach ($inputs as $data) {
                if (isset($data['id'])) {
                    // Find instance to trigger Activity Log
                    $currency = CurrencyConfig::find($data['id']);
                    
                    if ($currency) {
                        $currency->update([
                            'currency_type' => $data['currency_type'],
                            'symbol'        => $data['symbol'],
                            'digit_number'  => $data['digit_number'],
                            'price_total'   => $data['price_total'],
                            'price_single'  => $data['price_single'],
                            'branch'        => $data['branch'],
                            'is_active'     => isset($data['is_active']) ? 1 : 0,
                        ]);
                    }
                } else {
                    // Create new row
                    if (!empty($data['currency_type'])) {
                        CurrencyConfig::create([
                            'currency_type' => $data['currency_type'],
                            'symbol'        => $data['symbol'],
                            'digit_number'  => $data['digit_number'],
                            'price_total'   => $data['price_total'],
                            'price_single'  => $data['price_single'],
                            'branch'        => $data['branch'],
                            'is_active'     => isset($data['is_active']) ? 1 : 0,
                        ]);
                    }
                }
            }
        });

        return back()->with('success', __('currency.saved'));
    }

    public function destroy($id)
    {
        try {
            $currency = CurrencyConfig::find($id);
            
            if (!$currency) {
                return back()->with('error', __('currency.not_found') ?? 'Item not found');
            }

            $currency->delete();
            return back()->with('success', __('currency.deleted') ?? 'Deleted successfully');

        } catch (QueryException $e) {
            // Check for Postgres Foreign Key Violation (23503)
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
}