<?php

namespace App\Http\Controllers\Accountant; // ✅ Namespace set to Accountant folder

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Cashbox;
use App\Models\CurrencyConfig;
use App\Models\Transaction;
use Illuminate\Http\Request;

class PayingController extends Controller // ✅ Class name matches filename 'PayingController'
{
    public function index(Request $request)
    {
        // Fetch Data for the View
        $accounts = Account::select('id', 'name', 'code', 'currency', 'profile_picture', 'supported_currencies')->get();
        $cashboxes = Cashbox::select('id', 'name', 'currency_id')->get();
        $currencies = CurrencyConfig::select('id', 'currency_type', 'code', 'price_sell', 'price_single')->get();

        // Query Transactions (Only Payments)
        $query = Transaction::with(['account', 'user', 'cashbox', 'currency'])
            ->where('type', 'pay'); // 🟢 FILTER ONLY PAYMENTS

        // Apply Search Filters
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('id', 'like', "%$search%")
                  ->orWhereHas('account', fn($a) => $a->where('name', 'like', "%$search%"));
            });
        }

        $transactions = $query->latest()->paginate(10);

        // ✅ Pointing to the correct view folder
        return view('accountant.paying.index', compact('accounts', 'cashboxes', 'currencies', 'transactions'));
    }

    public function store(Request $request)
    {
        // VALIDATION
        $request->validate([
            'account_id' => 'required',
            'amount' => 'required|numeric|min:0',
            'currency_id' => 'required',
            'cashbox_id' => 'required',
            'type' => 'required|in:pay', // Ensure it's a payment
        ]);

        // LOGIC TO SAVE TRANSACTION
        Transaction::create([
            'account_id' => $request->account_id,
            'amount' => $request->amount,
            'total' => $request->total ?? $request->amount, // Handle total calculation
            'currency_id' => $request->currency_id,
            'cashbox_id' => $request->cashbox_id,
            'type' => 'pay', // Force type to pay
            'exchange_rate' => $request->exchange_rate ?? 1,
            'discount' => $request->discount ?? 0,
            'note' => $request->note,
            'manual_date' => $request->manual_date ?? now(),
            'statement_id' => $request->statement_id,
            'giver_name' => $request->giver_name,
            'giver_mobile' => $request->giver_mobile,
            'receiver_name' => $request->receiver_name,
            'receiver_mobile' => $request->receiver_mobile,
            'user_id' => auth()->id(),
        ]);
        
        return redirect()->back()->with('success', __('accountant.save_success'));
    }

    public function destroy($id)
    {
        Transaction::findOrFail($id)->delete();
        return redirect()->back()->with('success', __('accountant.delete_success'));
    }

    public function bulkDelete(Request $request)
    {
        $ids = json_decode($request->ids);
        if (!empty($ids)) {
            Transaction::whereIn('id', $ids)->delete();
        }
        return redirect()->back()->with('success', __('accountant.delete_success'));
    }
    
    // Add other methods (edit, update, trash, restore, pdf) as needed...
    public function edit($id) { /* ... */ }
    public function update(Request $request, $id) { /* ... */ }
    public function trash() { /* ... */ }
    public function restore($id) { /* ... */ }
    public function forceDelete($id) { /* ... */ }
    public function pdf() { /* ... */ }
}