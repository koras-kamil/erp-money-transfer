<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Capital;
use App\Models\CurrencyConfig;
use App\Models\User;
use App\Models\Setting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Mccarlosen\LaravelMpdf\Facades\LaravelMpdf as PDF;

class CapitalController extends Controller
{
    /**
     * 1. INDEX PAGE
     */
    public function index()
    {
        $capitals = Capital::with(['owner', 'currency', 'creator'])
                    ->orderBy('id', 'asc') 
                    ->paginate(15);

        $owners = User::all();
        $currencies = CurrencyConfig::where('is_active', true)->get();
        $totalShareUsed = Capital::sum('share_percentage');

        return view('capital.index', compact('capitals', 'owners', 'currencies', 'totalShareUsed'));
    }

    /**
     * 2. STORE / UPDATE (With Smart Operator Logic)
     */
    public function store(Request $request)
    {
        $inputs = $request->input('capitals', []);

        if (empty($inputs)) {
            return back()->with('error', __('No data to save.'));
        }

        try {
            DB::transaction(function () use ($inputs) {
                // Filter valid inputs
                $activeInputs = collect($inputs)->filter(function($row) {
                    return !empty($row['amount']) || !empty($row['share_percentage']);
                });

                // Validation: Check 100% Share Limit
                $updatingIds = $activeInputs->pluck('id')->filter()->toArray();
                $dbSharesExcludingUpdates = Capital::whereNotIn('id', $updatingIds)->sum('share_percentage');
                $incomingSharesTotal = $activeInputs->sum(fn($i) => floatval($i['share_percentage'] ?? 0));
                $totalShares = round($dbSharesExcludingUpdates + $incomingSharesTotal, 2);

                if ($totalShares > 100.00) {
                    throw new \Exception(__('capital.share_limit_error', [
                        'current' => $dbSharesExcludingUpdates, 
                        'adding' => $incomingSharesTotal
                    ]));
                }

                // Get System Base Currency
                $baseCurrencyId = Setting::where('key', 'base_currency_id')->value('value');

                foreach ($inputs as $data) {
                    if (empty($data['amount']) && empty($data['share_percentage'])) continue;

                    // Clean numbers
                    $amount = isset($data['amount']) ? str_replace(',', '', $data['amount']) : 0;
                    $share  = !empty($data['share_percentage']) ? $data['share_percentage'] : 0;

                    $currency = CurrencyConfig::find($data['currency_id']);
                    
                    // --- SMART CALCULATION ---
                    if ($baseCurrencyId && $data['currency_id'] == $baseCurrencyId) {
                        $exchangeRate = 1;
                        $balanceUsd = $amount;
                    } else {
                        $exchangeRate = $currency ? $currency->price_single : 1;
                        
                        // Auto-Detect Operator: If Rate > 2.0 (e.g. IQD), DIVIDE. Else MULTIPLY.
                        $operator = ($exchangeRate > 2.0) ? '/' : '*';

                        if ($operator == '*') {
                            $balanceUsd = bcmul($amount, $exchangeRate, 4);
                        } else {
                            $balanceUsd = ($exchangeRate > 0) ? bcdiv($amount, $exchangeRate, 4) : 0;
                        }
                    }

                    $saveData = [
                        'owner_id'         => $data['owner_id'],
                        'share_percentage' => $share,
                        'amount'           => $amount,
                        'currency_id'      => $data['currency_id'],
                        'exchange_rate'    => $exchangeRate,
                        'balance_usd'      => $balanceUsd,
                        'date'             => $data['date'] ?? now(),
                    ];

                    if (isset($data['id']) && !empty($data['id'])) {
                        $capital = Capital::find($data['id']);
                        if($capital) $capital->update($saveData);
                    } else {
                        $saveData['created_by'] = Auth::id();
                        Capital::create($saveData);
                    }
                }
            });

            return back()->with('success', __('capital.saved_success'));

        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    /**
     * 3. SINGLE SOFT DELETE
     */
    public function destroy($id)
    {
        Capital::findOrFail($id)->delete();
        return back()->with('success', __('Deleted successfully'));
    }

    /**
     * 4. BULK SOFT DELETE (For Index Page)
     */
    public function bulkDelete(Request $request)
    {
        $ids = json_decode($request->input('ids', '[]'), true);
        if (!empty($ids) && is_array($ids)) {
            Capital::whereIn('id', $ids)->delete();
            return back()->with('success', __('capital.delete_selected'));
        }
        return back()->with('error', __('No items selected'));
    }

    /**
     * 5. TRASH VIEW
     */
   public function trash()
    {
        $capitals = Capital::onlyTrashed()
            ->with(['owner', 'currency']) // Load relations for the trash table
            ->orderBy('deleted_at', 'desc')
            ->paginate(15); // <--- CHANGED from get() to paginate(15)

        return view('capital.trash', compact('capitals'));
    }

    /**
     * 6. SINGLE RESTORE
     */
    public function restore($id)
    {
        $capital = Capital::onlyTrashed()->findOrFail($id);
        $capital->restore();
        return back()->with('success', __('Restored successfully'));
    }

    /**
     * 7. BULK RESTORE (For Trash Page)
     */
    public function bulkRestore(Request $request)
    {
        $ids = json_decode($request->input('ids', '[]'), true);
        if (!empty($ids) && is_array($ids)) {
            Capital::onlyTrashed()->whereIn('id', $ids)->restore();
            return back()->with('success', __('capital.restore_selected'));
        }
        return back()->with('error', __('No items selected'));
    }

    /**
     * 8. SINGLE FORCE DELETE
     */
    public function forceDelete($id)
    {
        $capital = Capital::onlyTrashed()->findOrFail($id);
        $capital->forceDelete();
        return back()->with('success', __('capital.perm_delete'));
    }

    /**
     * 9. BULK FORCE DELETE (For Trash Page)
     */
    public function bulkForceDelete(Request $request)
    {
        $ids = json_decode($request->input('ids', '[]'), true);
        if (!empty($ids) && is_array($ids)) {
            $items = Capital::onlyTrashed()->whereIn('id', $ids)->get();
            foreach($items as $item) {
                $item->forceDelete();
            }
            return back()->with('success', __('capital.perm_delete'));
        }
        return back()->with('error', __('No items selected'));
    }

    /**
     * 10. DOWNLOAD PDF
     */
    public function downloadPdf()
    {
        $capitals = Capital::with(['owner', 'currency'])->orderBy('id', 'asc')->get();
        $totalBalance = Capital::sum('balance_usd');
        $totalShares = Capital::sum('share_percentage');

        $data = [
            'title' => __('capital.report_title'),
            'date' => date('Y-m-d'),
            'capitals' => $capitals,
            'totalBalance' => $totalBalance,
            'totalShares' => $totalShares,
        ];

        $pdf = PDF::loadView('capital.pdf', $data, [], [
            'mode' => 'utf-8', 
            'format' => 'A4',
            'orientation' => 'L' 
        ]);

        return $pdf->stream('capital-report.pdf');
    }
}