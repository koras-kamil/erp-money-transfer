<?php

namespace App\Http\Controllers\Accountant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Receiving;
use App\Models\Account;
use App\Models\CashBox;
use App\Models\CurrencyConfig; 
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ReceivingController extends Controller
{
    public function index(Request $request)
    {
        $query = Receiving::with(['account', 'currency', 'cashbox', 'user', 'city', 'neighborhood'])->latest();

        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->whereHas('account', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('manual_code', 'like', "%{$search}%");
            });
        }

        $transactions = $query->paginate(15);
        $currencies = CurrencyConfig::where('is_active', true)->get();

        // 🟢 FETCH ACCOUNTS WITH SUPPORTED CURRENCIES
        $accounts = Account::with(['city', 'neighborhood'])
            ->where('is_active', true)
            ->get()
            ->map(function ($acc) {
                
                // Decode supported currencies from JSON to Array
                $supported = $acc->supported_currency_ids;
                if (is_string($supported)) {
                    $supported = json_decode($supported, true) ?? [];
                } elseif (!is_array($supported)) {
                    $supported = [];
                }

                return [
                    'id' => $acc->id,
                    'name' => $acc->name,
                    'code' => $acc->manual_code ?? $acc->code,
                    'avatar' => $acc->profile_picture ? asset('storage/'.$acc->profile_picture) : null,
                    'mobile' => $acc->mobile_number_1 ?? '-', 
                    
                    // Location info
                    'city_name' => $acc->city ? $acc->city->city_name : null,
                    'neighborhood_name' => $acc->neighborhood ? $acc->neighborhood->neighborhood_name : null,
                    'address_details' => $acc->location ?? null, 

                    // Currency info
                    'default_currency_id' => $acc->currency_id, 
                    'supported_currencies' => $supported, // ✅ Sent to frontend
                    
                    'debt_limit' => number_format($acc->debt_limit ?? 0, 2),
                ];
            });

        $cashboxes = CashBox::where('is_active', true)->get();

        return view('accountant.receiving.index', compact('transactions', 'accounts', 'cashboxes', 'currencies'));
    }

    public function store(Request $request)
    {
        // Remove commas from numbers
        $cleanData = $request->all();
        $fieldsToClean = ['amount', 'discount', 'exchange_rate'];
        foreach ($fieldsToClean as $field) {
            if (isset($cleanData[$field])) {
                $cleanData[$field] = str_replace(',', '', $cleanData[$field]);
            }
        }
        $request->replace($cleanData);

        $request->validate([
            'account_id'  => 'required|exists:accounts,id',
            'amount'      => 'required|numeric|min:0',
            'currency_id' => 'required|exists:currency_configs,id',
            'cashbox_id'  => 'required|exists:cash_boxes,id',
        ]);

        try {
            DB::transaction(function () use ($request) {
                $selectedCurrency = CurrencyConfig::findOrFail($request->currency_id);
                $rate = $request->exchange_rate ?? $selectedCurrency->price_single ?? 1;

                Receiving::create([
                    'statement_id'   => $request->statement_id ?? 'REC-'.time(),
                    'manual_date'    => $request->manual_date ?? now(),
                    'invoice_type'   => $request->type == 'pay' ? 'payment' : 'normal',
                    'account_id'     => $request->account_id,
                    'amount'         => $request->amount,
                    'currency_id'    => $selectedCurrency->id,
                    'exchange_rate'  => $rate,
                    'discount'       => $request->discount ?? 0,
                    'city_id'         => $request->city_id,
                    'neighborhood_id' => $request->neighborhood_id,
                    'giver_name'      => $request->giver_name,
                    'giver_mobile'    => $request->giver_mobile,
                    'receiver_name'   => $request->receiver_name,
                    'receiver_mobile' => $request->receiver_mobile,
                    'note'            => $request->note,
                    'cashbox_id'     => $request->cashbox_id,
                    'user_id'        => Auth::id(),
                ]);

                $cashbox = CashBox::lockForUpdate()->find($request->cashbox_id);
                if ($request->type == 'pay') {
                    $cashbox->balance -= $request->amount;
                } else {
                    $cashbox->balance += $request->amount;
                }
                $cashbox->save();
            });

            return redirect()->back()->with('success', __('accountant.save_success'));
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['msg' => __('accountant.error') . ': ' . $e->getMessage()]);
        }
    }

    public function destroy($id)
    {
        try {
            DB::transaction(function () use ($id) {
                $receiving = Receiving::findOrFail($id);
                $cashbox = CashBox::lockForUpdate()->find($receiving->cashbox_id);
                if ($cashbox) {
                    if ($receiving->invoice_type == 'payment') {
                        $cashbox->balance += $receiving->amount;
                    } else {
                        $cashbox->balance -= $receiving->amount;
                    }
                    $cashbox->save();
                }
                $receiving->delete();
            });
            return redirect()->back()->with('success', __('accountant.delete_success'));
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['msg' => $e->getMessage()]);
        }
    }

    public function pdf()
{
    // Fetch all transactions for the PDF (or filter as needed)
    $transactions = Receiving::with(['account', 'currency', 'cashbox', 'user', 'city', 'neighborhood'])
                    ->latest()
                    ->get();

    // You need to create this view file: resources/views/accountant/receiving/pdf.blade.php
    return view('accountant.receiving.pdf', compact('transactions'));
}
}