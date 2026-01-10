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
        // Use a transaction to ensure all data is saved correctly
        DB::transaction(function () use ($request) {
            $inputs = $request->input('currencies');

            // 1. Loop through each row from the sheet
            foreach ($inputs as $data) {
                if (isset($data['id'])) {
                    // Update existing row
                    CurrencyConfig::where('id', $data['id'])->update([
                        'currency_type' => $data['currency_type'],
                        'symbol'        => $data['symbol'],
                        'digit_number'  => $data['digit_number'],
                        'price_total'   => $data['price_total'],
                        'price_single'  => $data['price_single'],
                        'price_sell'    => $data['price_sell'],
                        'branch'        => $data['branch'],
                        'is_active'     => isset($data['is_active']) ? 1 : 0,
                    ]);
                } else {
                    // Create new row (if ID is null)
                    if (!empty($data['currency_type'])) { // Simple validation
                        CurrencyConfig::create([
                            'currency_type' => $data['currency_type'],
                            'symbol'        => $data['symbol'],
                            'digit_number'  => $data['digit_number'],
                            'price_total'   => $data['price_total'],
                            'price_single'  => $data['price_single'],
                            'price_sell'    => $data['price_sell'],
                            'branch'        => $data['branch'],
                            'is_active'     => isset($data['is_active']) ? 1 : 0,
                        ]);
                    }
                }
            }
        });

return back()->with('success', __('currency.saved'));    }

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

