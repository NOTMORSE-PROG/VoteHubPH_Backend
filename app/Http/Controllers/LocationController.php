<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Region;
use App\Models\Province;
use App\Models\City;
use App\Models\Barangay;

class LocationController extends Controller
{
    /**
     * Get all regions (cached for 1 hour)
     */
    public function getRegions()
    {
        $regions = cache()->remember('regions', 3600, function () {
            return Region::select('id', 'code', 'name', 'psgc_code')
                ->orderBy('name')
                ->get();
        });

        return response()->json($regions);
    }

    /**
     * Get cities (not districts), optionally filtered by region (cached for 1 hour)
     */
    public function getCities(Request $request)
    {
        $regionId = $request->get('region_id');
        $cacheKey = $regionId ? "cities_region_{$regionId}" : 'cities_all';

        $cities = cache()->remember($cacheKey, 3600, function () use ($regionId) {
            $query = City::select('id', 'region_id', 'province_id', 'parent_city_id', 'name', 'type', 'is_district', 'psgc_code')
                ->where('is_district', false); // Only return main cities, not districts

            if ($regionId) {
                $query->where('region_id', $regionId);
            }

            $cities = $query->orderBy('name')->get();

            // Add has_districts flag to each city
            $cities->each(function ($city) {
                $city->has_districts = City::where('parent_city_id', $city->id)
                    ->where('is_district', true)
                    ->exists();
            });

            return $cities;
        });

        return response()->json($cities);
    }

    /**
     * Get districts for a specific city (cached for 1 hour)
     */
    public function getDistricts(Request $request)
    {
        $cityId = $request->get('city_id');

        if (!$cityId) {
            return response()->json([]);
        }

        $cacheKey = "districts_city_{$cityId}";

        $districts = cache()->remember($cacheKey, 3600, function () use ($cityId) {
            return City::select('id', 'region_id', 'parent_city_id', 'name', 'type', 'is_district', 'psgc_code')
                ->where('parent_city_id', $cityId)
                ->where('is_district', true)
                ->orderBy('psgc_code') // Sort by PSGC code for numerical order (First, Second, Third, etc.)
                ->get();
        });

        return response()->json($districts);
    }

    /**
     * Get barangays, optionally filtered by city or district (cached for 1 hour)
     */
    public function getBarangays(Request $request)
    {
        // Support both city_id and district_id parameters
        // district_id takes precedence if both are provided
        $districtId = $request->get('district_id');
        $cityId = $districtId ?: $request->get('city_id');

        $cacheKey = $cityId ? "barangays_city_{$cityId}" : 'barangays_all';

        $barangays = cache()->remember($cacheKey, 3600, function () use ($cityId) {
            $query = Barangay::select('id', 'city_id', 'name', 'psgc_code');

            if ($cityId) {
                $query->where('city_id', $cityId);
            }

            // Sort by psgc_code instead of name for better performance with numbered barangays
            return $query->orderBy('psgc_code')->get();
        });

        return response()->json($barangays);
    }

    /**
     * Get provinces, optionally filtered by region
     */
    public function getProvinces(Request $request)
    {
        $query = Province::select('id', 'region_id', 'name', 'psgc_code')
            ->with('region:id,name,code');

        if ($request->has('region_id')) {
            $query->where('region_id', $request->region_id);
        }

        $provinces = $query->orderBy('name')->get();

        return response()->json($provinces);
    }
}
