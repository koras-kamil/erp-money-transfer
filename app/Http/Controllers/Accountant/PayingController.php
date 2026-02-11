<?php

namespace App\Http\Controllers\Accountant;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Cashbox;
use App\Models\CurrencyConfig;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PayingController extends Controller
{
    public function index(Request $request)
    {
        // 🟢 Global Pagination
        $limit = defined('PER_PAGE') ? PER_PAGE : 10;

        $query = Transaction::with(['account', 'currency', 'cashbox', 'user'])
            ->where('type', 'pay') 
            ->latest();

        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->whereHas('account', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('manual_code', 'like', "%{$search}%");
            });
        }

        $transactions = $query->paginate($limit);
        $accounts = Account::all(); // Simplified fetch for consistency
        $currencies = CurrencyConfig::where('is_active', true)->get();
        $cashboxes = Cashbox::where('is_active', true)->get();

        return view('accountant.paying.index', compact('transactions', 'accounts', 'cashboxes', 'currencies'));
    }

    public function store(Request $request)
    {
        $this->cleanInputs($request);

        $validated = $request->validate([
            'account_id'    => 'required|exists:accounts,id',
            'amount'        => 'required|numeric|min:0',
            'currency_id'   => 'required|exists:currency_configs,id',
            'cashbox_id'    => 'required|exists:cash_boxes,id',
            'exchange_rate' => 'nullable|numeric',
            'discount'      => 'nullable|numeric',
            'manual_date'   => 'nullable|date',
            'statement_id'  => 'nullable|string',
            'note'          => 'nullable|string',
            'giver_name'    => 'nullable|string',
            'giver_mobile'  => 'nullable|string',
            'receiver_name' => 'nullable|string',
            'receiver_mobile'=> 'nullable|string',
            'profit_amount' => 'nullable|numeric',
            'spending_amount'=> 'nullable|numeric',
        ]);

        DB::transaction(function () use ($validated, $request) {
            Transaction::create([
                'user_id'       => Auth::id(),
                'type'          => 'pay',
                'account_id'    => $validated['account_id'],
                'currency_id'   => $validated['currency_id'],
                'cashbox_id'    => $validated['cashbox_id'],
                'amount'        => $validated['amount'],
                'total'         => $request->total ?? ($validated['amount'] - ($validated['discount'] ?? 0)),
                'exchange_rate' => $validated['exchange_rate'] ?? 1,
                'discount'      => $validated['discount'] ?? 0,
                'manual_date'   => $validated['manual_date'] ?? now(),
                'statement_id'  => $validated['statement_id'],
                'note'          => $validated['note'],
                'giver_name'    => $validated['giver_name'],
                'giver_mobile'  => $validated['giver_mobile'],
                'receiver_name' => $validated['receiver_name'],
                'receiver_mobile'=> $validated['receiver_mobile'],
            ]);

            // Update Cashbox (Subtract Money)
            $cashbox = Cashbox::lockForUpdate()->find($validated['cashbox_id']);
            if ($cashbox) {
                $cashbox->balance -= $validated['amount'];
                $cashbox->save();
            }
        });

        return redirect()->route('accountant.paying.index')->with('success', __('Transaction created successfully.'));
    }

    public function update(Request $request, $id)
    {
        $transaction = Transaction::findOrFail($id);
        $this->cleanInputs($request);

        $validated = $request->validate([
            'account_id'    => 'required|exists:accounts,id',
            'amount'        => 'required|numeric|min:0',
            'currency_id'   => 'required|exists:currency_configs,id',
            'cashbox_id'    => 'required|exists:cash_boxes,id',
            'exchange_rate' => 'nullable|numeric',
            'discount'      => 'nullable|numeric',
            'manual_date'   => 'nullable|date',
            'statement_id'  => 'nullable|string',
            'note'          => 'nullable|string',
            'giver_name'    => 'nullable|string',
            'giver_mobile'  => 'nullable|string',
            'receiver_name' => 'nullable|string',
            'receiver_mobile'=> 'nullable|string',
        ]);

        $transaction->update([
            'account_id'    => $validated['account_id'],
            'currency_id'   => $validated['currency_id'],
            'cashbox_id'    => $validated['cashbox_id'],
            'amount'        => $validated['amount'],
            'total'         => $request->total ?? ($validated['amount'] - ($validated['discount'] ?? 0)),
            'exchange_rate' => $validated['exchange_rate'] ?? 1,
            'discount'      => $validated['discount'] ?? 0,
            'manual_date'   => $validated['manual_date'],
            'statement_id'  => $validated['statement_id'],
            'note'          => $validated['note'],
            'giver_name'    => $validated['giver_name'],
            'giver_mobile'  => $validated['giver_mobile'],
            'receiver_name' => $validated['receiver_name'],
            'receiver_mobile'=> $validated['receiver_mobile'],
        ]);

        return redirect()->route('accountant.paying.index')->with('success', __('Transaction updated successfully.'));
    }

    public function destroy($id)
    {
        DB::transaction(function () use ($id) {
            $transaction = Transaction::findOrFail($id);
            // Reverse Cashbox (Add Money Back)
            $cashbox = Cashbox::lockForUpdate()->find($transaction->cashbox_id);
            if ($cashbox) {
                $cashbox->balance += $transaction->amount;
                $cashbox->save();
            }
            $transaction->delete();
        });
        return redirect()->back()->with('success', __('Transaction deleted successfully.'));
    }

    public function bulkDelete(Request $request)
    {
        $ids = json_decode($request->ids, true);
        if (!empty($ids)) {
            Transaction::whereIn('id', $ids)->delete();
            return redirect()->back()->with('success', __('Selected transactions deleted successfully.'));
        }
        return redirect()->back()->with('error', __('No items selected.'));
    }

    public function trash()
    {
        $transactions = Transaction::onlyTrashed()->with(['account', 'user'])->paginate(20);
        return view('accountant.paying.trash', compact('transactions'));
    }

    private function cleanInputs(Request $request)
    {
        $cleanData = $request->all();
        $fields = ['amount', 'discount', 'exchange_rate', 'total', 'profit_amount', 'spending_amount'];
        foreach ($fields as $field) {
            if (isset($cleanData[$field])) {
                $cleanData[$field] = str_replace(',', '', $cleanData[$field]);
            }
        }
        $request->replace($cleanData);
    }
}