<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\City;
use App\Models\Neighborhood;
use App\Models\CurrencyConfig;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class AccountController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // 1. Eager Loading (The Performance Fix)
        // We load all relationships at once to prevent N+1 issues.
        // This executes 5-6 fixed queries instead of 20+.
        $query = Account::with(['currency', 'city', 'neighborhood', 'branch', 'creator']); 

        // 2. Filter Logic (AJAX Search)
        if ($request->ajax()) {
            $textColumns = ['code', 'name', 'manual_code', 'mobile_number_1', 'secondary_name'];
            foreach ($textColumns as $col) {
                if ($request->filled($col)) {
                    $query->where($col, 'like', '%' . $request->input($col) . '%');
                }
            }
            if ($request->filled('currency_id')) $query->where('currency_id', $request->input('currency_id'));
            if ($request->filled('branch_id')) $query->where('branch_id', $request->input('branch_id'));
            
            if ($request->filled('sort')) {
                $query->orderBy($request->sort, $request->input('direction', 'asc'));
            } else {
                $query->latest();
            }
        } else {
            $query->latest();
        }

        // 3. Paginate
        $accounts = $query->paginate(15);

        // 4. Transform Data for Frontend
        // Accessing relations here is fast because they are already loaded in Step 1.
        $accounts->getCollection()->transform(function ($acc) {
            return [
                'id' => $acc->id,
                'image_url' => $acc->profile_picture ? asset('storage/' . $acc->profile_picture) : null,
                'initial' => substr($acc->name, 0, 1),
                'code' => $acc->code,
                'manual_code' => $acc->manual_code,
                'name' => $acc->name,
                'secondary_name' => $acc->secondary_name,
                'account_type' => __('account.' . $acc->account_type),
                'account_type_raw' => $acc->account_type,
                'mobile_number_1' => $acc->mobile_number_1,
                'mobile_number_2' => $acc->mobile_number_2,
                
                // Relationships
                'currency_text' => $acc->currency->currency_type ?? '-',
                'currency_id' => $acc->currency_id,
                'branch_id' => $acc->branch_id,
                'branch_text' => $acc->branch ? $acc->branch->name : '-', 
                'city_text' => $acc->city ? $acc->city->city_name : '-',
                'city_id' => $acc->city_id,
                'neighborhood_text' => $acc->neighborhood ? $acc->neighborhood->neighborhood_name : '-',
                'neighborhood_id' => $acc->neighborhood_id,
                
                'debt_limit' => $acc->debt_limit,
                'debt_due_time' => $acc->debt_due_time,
                'location' => $acc->location,
                'is_active' => (bool) $acc->is_active, 
                'created_at' => $acc->created_at,
                'creator_name' => $acc->creator ? $acc->creator->name : 'SYSTEM', 
                
                // URLs
                'edit_url' => route('accounts.update', $acc->id),
                'delete_url' => route('accounts.destroy', $acc->id),
            ];
        });

        if ($request->ajax()) {
            return response()->json($accounts);
        }

        // Load Dropdown Data (These 4 queries run once per page load)
        $cities = City::orderBy('city_name')->get();
        $neighborhoods = Neighborhood::orderBy('neighborhood_name')->get();
        $currencies = CurrencyConfig::where('is_active', true)->get();
        $branches = Branch::all(); 
        
        // Auto Code Sequence
        $nextId = (Account::withTrashed()->max('id') ?? 0) + 1;
        $autoCode = 'ACC-' . str_pad($nextId, 5, '0', STR_PAD_LEFT);

        return view('accounts.index', compact('cities', 'neighborhoods', 'currencies', 'branches', 'autoCode', 'accounts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|unique:accounts,code',
            'currency_id' => 'required',
            'account_type' => 'required',
        ]);

        $data = $request->except('profile_picture');
        $data['debt_limit'] = $request->input('debt_limit') ?? 0;
        $data['debt_due_time'] = $request->input('debt_due_time') ?? 0;
        $data['created_by'] = Auth::id();
        $data['branch_id'] = $request->input('branch_id');
        $data['is_active'] = $request->has('is_active') ? 1 : 0;

        if ($request->hasFile('profile_picture')) {
            $data['profile_picture'] = $request->file('profile_picture')->store('accounts', 'public');
        }

        Account::create($data);
        return back()->with('success', __('account.saved'));
    }

    public function update(Request $request, $id)
    {
        $account = Account::findOrFail($id);
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|unique:accounts,code,'.$id,
            'currency_id' => 'required',
        ]);

        $data = $request->except('profile_picture');
        $data['debt_limit'] = $request->input('debt_limit') ?? 0;
        $data['debt_due_time'] = $request->input('debt_due_time') ?? 0;
        $data['is_active'] = $request->has('is_active') ? 1 : 0;
        $data['branch_id'] = $request->input('branch_id');

        if ($request->hasFile('profile_picture')) {
            if($account->profile_picture) Storage::disk('public')->delete($account->profile_picture);
            $data['profile_picture'] = $request->file('profile_picture')->store('accounts', 'public');
        }

        $account->update($data);
        return back()->with('success', __('account.updated'));
    }

    public function destroy($id)
    {
        $account = Account::findOrFail($id);
        $account->delete(); // Soft delete
        return back()->with('success', __('account.deleted'));
    }

    public function bulkDelete(Request $request)
    {
        $request->validate(['ids' => 'required']);
        $ids = is_array($request->ids) ? $request->ids : json_decode($request->ids, true);
        if (is_array($ids) && count($ids) > 0) {
            Account::whereIn('id', $ids)->delete();
            return back()->with('success', __('account.deleted_selected'));
        }
        return back()->with('error', __('account.none_selected'));
    }

    // --- TRASH FUNCTIONS (Optimized) ---

    public function trash(Request $request)
    {
        // 1. Eager Load Relations in Trash too (Fixes N+1 in Trash)
        $query = Account::onlyTrashed()->with(['currency', 'city', 'neighborhood', 'branch', 'creator']);

        if ($request->ajax()) {
            $textColumns = ['code', 'name', 'manual_code', 'mobile_number_1'];
            foreach ($textColumns as $col) {
                if ($request->filled($col)) {
                    $query->where($col, 'like', '%' . $request->input($col) . '%');
                }
            }
            if ($request->filled('sort')) {
                $query->orderBy($request->sort, $request->input('direction', 'asc'));
            } else {
                $query->latest('deleted_at');
            }
        } else {
            $query->latest('deleted_at');
        }

        $accounts = $query->paginate(15);

        // Transform Trash Data
        $accounts->getCollection()->transform(function ($acc) {
            return [
                'id' => $acc->id,
                'image_url' => $acc->profile_picture ? asset('storage/' . $acc->profile_picture) : null,
                'initial' => substr($acc->name, 0, 1),
                'code' => $acc->code,
                'manual_code' => $acc->manual_code,
                'name' => $acc->name,
                'secondary_name' => $acc->secondary_name,
                'account_type' => __('account.' . $acc->account_type),
                
                // Relations
                'currency_text' => $acc->currency->currency_type ?? '-',
                'currency_id' => $acc->currency_id,
                'branch_id' => $acc->branch_id,
                'branch_text' => $acc->branch ? $acc->branch->name : '-',
                'city_text' => $acc->city ? $acc->city->city_name : '-',
                'neighborhood_text' => $acc->neighborhood ? $acc->neighborhood->neighborhood_name : '-',
                'creator_name' => $acc->creator ? $acc->creator->name : 'SYSTEM',

                'deleted_at' => $acc->deleted_at->diffForHumans(),
                
                // Trash Actions
                'restore_url' => route('accounts.restore', $acc->id),
                'force_delete_url' => route('accounts.force-delete', $acc->id),
            ];
        });

        if ($request->ajax()) {
            return response()->json($accounts);
        }

        return view('accounts.trash', compact('accounts'));
    }

    public function restore($id)
    {
        $account = Account::onlyTrashed()->findOrFail($id);
        $account->restore();
        return redirect()->route('accounts.index')->with('success', __('account.restored'));
    }

    public function forceDelete($id)
    {
        $account = Account::onlyTrashed()->findOrFail($id);
        if ($account->profile_picture) {
            Storage::disk('public')->delete($account->profile_picture);
        }
        $account->forceDelete();
        return back()->with('success', __('account.permanently_deleted'));
    }

    // Bulk Force Delete
    public function bulkForceDelete(Request $request)
    {
        $request->validate(['ids' => 'required']);
        $ids = is_array($request->ids) ? $request->ids : json_decode($request->ids, true);
        
        if (is_array($ids) && count($ids) > 0) {
            $accounts = Account::onlyTrashed()->whereIn('id', $ids)->get();
            foreach ($accounts as $account) {
                if ($account->profile_picture) {
                    Storage::disk('public')->delete($account->profile_picture);
                }
                $account->forceDelete();
            }
            return back()->with('success', __('account.permanently_deleted'));
        }
        return back()->with('error', __('account.none_selected'));
    }

    // Bulk Restore
    public function bulkRestore(Request $request)
    {
        $request->validate(['ids' => 'required']);
        $ids = is_array($request->ids) ? $request->ids : json_decode($request->ids, true);
        
        if (is_array($ids) && count($ids) > 0) {
            Account::onlyTrashed()->whereIn('id', $ids)->restore();
            return back()->with('success', __('account.restored'));
        }
        return back()->with('error', __('account.none_selected'));
    }
}