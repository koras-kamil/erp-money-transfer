<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Neighborhood;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ZoneController extends Controller
{
    public function index()
    {
        $cities = City::with('creator')->latest()->get();
        $neighborhoods = Neighborhood::with(['city', 'creator'])->latest()->get();
        
        return view('zones.index', compact('cities', 'neighborhoods'));
    }

    // ==========================
    // CITIES LOGIC
    // ==========================
    public function storeCities(Request $request)
    {
        if ($request->has('cities') && is_array($request->cities)) {
            foreach ($request->cities as $data) {
                if (empty($data['city_name'])) continue;

                $payload = [
                    'code' => $data['code'] ?? null,
                    'city_name' => $data['city_name'],
                    'branch_id' => Auth::user()->branch_id, 
                ];

                // ✅ FIXED: Check if ID exists AND is a valid number
                // This prevents "new-123..." strings from crashing the SQL query
                if (isset($data['id']) && is_numeric($data['id'])) {
                    City::where('id', $data['id'])->update($payload);
                } else {
                    $payload['created_by'] = Auth::id();
                    City::create($payload);
                }
            }
            return redirect()->route('zones.index')->with(['success' => __('account.cities_saved'), 'active_tab' => 'cities']);
        }
        return back();
    }

    public function destroyCity($id)
    {
        City::findOrFail($id)->delete();
        return redirect()->route('zones.index')->with(['success' => __('account.city_deleted'), 'active_tab' => 'cities']);
    }

    // ==========================
    // NEIGHBORHOODS LOGIC
    // ==========================
    public function storeNeighborhoods(Request $request)
    {
        if ($request->has('neighborhoods') && is_array($request->neighborhoods)) {
            foreach ($request->neighborhoods as $data) {
                if (empty($data['neighborhood_name']) || empty($data['city_id'])) continue;

                $payload = [
                    'code' => $data['code'] ?? null,
                    'city_id' => $data['city_id'],
                    'neighborhood_name' => $data['neighborhood_name'],
                    'branch_id' => Auth::user()->branch_id, 
                ];

                // ✅ FIXED: Check if ID exists AND is a valid number
                if (isset($data['id']) && is_numeric($data['id'])) {
                    Neighborhood::where('id', $data['id'])->update($payload);
                } else {
                    $payload['created_by'] = Auth::id();
                    Neighborhood::create($payload);
                }
            }
            return redirect()->route('zones.index')->with(['success' => __('account.neighborhoods_saved'), 'active_tab' => 'neighborhoods']);
        }
        return back();
    }

    public function destroyNeighborhood($id)
    {
        Neighborhood::findOrFail($id)->delete();
        return redirect()->route('zones.index')->with(['success' => __('account.neighborhood_deleted'), 'active_tab' => 'neighborhoods']);
    }
}