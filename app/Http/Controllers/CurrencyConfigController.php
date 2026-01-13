<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CurrencyConfig; // Make sure you have this Model created
use Illuminate\Support\Facades\DB;

class CurrencyConfigController extends Controller
{
    public function index()
    {
        // Get all existing currencies to show in the sheet
        $currencies = CurrencyConfig::orderBy('id')->get();
        return view('currency.index', compact('currencies'));
    }

   public function store(Request $request)
{
    DB::transaction(function () use ($request) {
        $inputs = $request->input('currencies', []);

        foreach ($inputs as $data) {
            if (isset($data['id'])) {
                // ✅ FIX: Find the model instance first to trigger Activity Log
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
    $currency = CurrencyConfig::find($id);
    
    if ($currency) {
        $currency->delete();
        return back()->with('success', __('currency.deleted') ?? 'Deleted successfully');
    }

    return back()->with('error', __('currency.error') ?? 'Error deleting item');
}
}

