<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\City;
use App\Models\Neighborhood;
use App\Models\CurrencyConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class AccountController extends Controller
{
    public function index(Request $request)
    {
        // 1. AJAX Request: Data Table
        if ($request->ajax()) {
            $query = Account::with(['currency', 'city', 'neighborhood']);

            // --- A. Text Search ---
            $textColumns = ['code', 'manual_code', 'name', 'secondary_name', 'mobile_number_1', 'account_type'];
            foreach ($textColumns as $col) {
                if ($request->filled($col)) {
                    $val = trim($request->input($col));
                    $op = env('DB_CONNECTION') === 'pgsql' ? 'ILIKE' : 'LIKE';
                    $query->where($col, $op, "%{$val}%");
                }
            }

            // --- B. Numeric Search ---
            $numColumns = ['id', 'debt_limit', 'debt_due_time'];
            foreach ($numColumns as $col) {
                if ($request->filled($col)) {
                    $val = trim($request->input($col));
                    if (env('DB_CONNECTION') === 'pgsql') {
                        $query->whereRaw("CAST({$col} AS TEXT) LIKE ?", ["%{$val}%"]);
                    } else {
                        $query->where($col, 'LIKE', "%{$val}%");
                    }
                }
            }

            // --- C. Relationship Search ---
            if ($request->filled('currency_id')) {
                $val = trim($request->input('currency_id'));
                $query->whereHas('currency', fn($q) => $q->where('currency_type', 'like', "%$val%"));
            }
            if ($request->filled('city_id')) {
                $val = trim($request->input('city_id'));
                $query->whereHas('city', fn($q) => $q->where('city_name', 'like', "%$val%"));
            }
            if ($request->filled('neighborhood_id')) {
                $val = trim($request->input('neighborhood_id'));
                $query->whereHas('neighborhood', fn($q) => $q->where('neighborhood_name', 'like', "%$val%"));
            }

            // --- D. Sorting ---
            if ($request->filled('sort')) {
                $query->orderBy($request->sort, $request->input('direction', 'asc'));
            } else {
                $query->latest();
            }

            // --- E. Pagination ---
            $accounts = $query->paginate(15);

            // --- F. Transformation ---
            $accounts->getCollection()->transform(function ($acc) {
                return [
                    'id' => $acc->id,
                    'image_url' => $acc->profile_picture ? asset('storage/' . $acc->profile_picture) : null,
                    'initial' => substr($acc->name, 0, 1),
                    'code' => $acc->code,
                    'manual_code' => $acc->manual_code ?? '-',
                    'name' => $acc->name,
                    'secondary_name' => $acc->secondary_name ?? '',
                    'account_type' => __('account.' . $acc->account_type),
                    'account_type_raw' => $acc->account_type,
                    'mobile_number_1' => $acc->mobile_number_1 ?? '-',
                    'mobile_number_2' => $acc->mobile_number_2,
                    'currency_text' => $acc->currency->currency_type ?? '-',
                    'currency_id' => $acc->currency_id,
                    'city_text' => $acc->city ? $acc->city->city_name : '-',
                    'city_id' => $acc->city_id,
                    'neighborhood_text' => $acc->neighborhood ? $acc->neighborhood->neighborhood_name : '-',
                    'neighborhood_id' => $acc->neighborhood_id,
                    'debt_limit' => number_format($acc->debt_limit, 0),
                    'debt_due_time' => $acc->debt_due_time,
                    'location' => $acc->location,
                    'is_active' => (bool) $acc->is_active,
                    'edit_url' => route('accounts.update', $acc->id),
                    'delete_url' => route('accounts.destroy', $acc->id),
                ];
            });

            return response()->json($accounts);
        }

        // 2. Normal View Load (Dropdown Data)
        $cities = City::orderBy('city_name')->get();
        $neighborhoods = Neighborhood::orderBy('neighborhood_name')->get();
        $currencies = CurrencyConfig::where('is_active', true)->get();
        
        // Auto Code
        $nextId = (Account::max('id') ?? 0) + 1;
        $autoCode = 'ACC-' . str_pad($nextId, 5, '0', STR_PAD_LEFT);

        return view('accounts.index', compact('cities', 'neighborhoods', 'currencies', 'autoCode'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|unique:accounts,code',
            'currency_id' => 'required|exists:currency_configs,id',
            'account_type' => 'required',
            'profile_picture' => 'nullable|image|max:5120',
        ]);

        $data = $request->except('profile_picture');
        $data['created_by'] = Auth::id();
        // ✅ NEW: Assign Branch ID (Fixes foreign key issues if strict)
        $data['branch_id'] = Auth::user()->branch_id; 
        $data['is_active'] = $request->has('is_active');

        if ($request->hasFile('profile_picture')) {
            $data['profile_picture'] = $request->file('profile_picture')->store('accounts', 'public');
        }

        Account::create($data);
        return back()->with('success', __('Account created successfully'));
    }

    public function update(Request $request, $id)
    {
        $account = Account::findOrFail($id);
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|unique:accounts,code,'.$id,
            'currency_id' => 'required|exists:currency_configs,id',
        ]);

        $data = $request->except('profile_picture');
        $data['is_active'] = $request->has('is_active');

        if ($request->hasFile('profile_picture')) {
            if($account->profile_picture) Storage::disk('public')->delete($account->profile_picture);
            $data['profile_picture'] = $request->file('profile_picture')->store('accounts', 'public');
        }

        $account->update($data);
        return back()->with('success', __('Account updated successfully'));
    }

    public function destroy($id)
    {
        $account = Account::findOrFail($id);
        if($account->profile_picture) Storage::disk('public')->delete($account->profile_picture);
        $account->delete();
        return back()->with('success', __('Account deleted successfully'));
    }

    public function bulkDelete(Request $request)
    {
        $request->validate(['ids' => 'required']); // Accepts array or json string
        
        // Handle if ID is sent as JSON string or Array
        $ids = is_array($request->ids) ? $request->ids : json_decode($request->ids, true);

        if (is_array($ids) && count($ids) > 0) {
            $accounts = Account::whereIn('id', $ids)->get();
            foreach($accounts as $acc) {
                if($acc->profile_picture) Storage::disk('public')->delete($acc->profile_picture);
                $acc->delete();
            }
            return back()->with('success', __('Selected accounts deleted successfully'));
        }
        return back()->with('error', __('No items selected'));
    }
}