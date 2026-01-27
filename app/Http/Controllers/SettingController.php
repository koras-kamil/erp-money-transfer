<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;
use App\Models\CurrencyConfig;

class SettingController extends Controller
{
    public function index()
    {
        $currencies = CurrencyConfig::where('is_active', true)->get();
        $currentBaseId = Setting::getValue('base_currency_id');

        return view('settings.index', compact('currencies', 'currentBaseId'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'base_currency_id' => 'required|exists:currency_configs,id',
        ]);

        Setting::setValue('base_currency_id', $request->base_currency_id);

        return back()->with('success', __('Settings updated successfully'));
    }
}