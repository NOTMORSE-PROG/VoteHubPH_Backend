<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\City;
use App\Models\Region;

class DistrictsSeeder extends Seeder
{
    /**
     * Add legislative districts for major Philippine cities
     */
    public function run(): void
    {
        $ncr = Region::where('code', 'NCR')->first();
        $region7 = Region::where('code', 'VII')->first();
        $region11 = Region::where('code', 'XI')->first();

        // Get parent cities
        $manila = City::where('name', 'Manila')->where('is_district', false)->first();
        $quezonCity = City::where('name', 'Quezon City')->where('is_district', false)->first();
        $caloocan = City::where('name', 'Caloocan')->where('is_district', false)->first();
        $pasig = City::where('name', 'Pasig')->where('is_district', false)->first();
        $cebuCity = City::where('name', 'Cebu City')->where('is_district', false)->first();
        $davaoCity = City::where('name', 'Davao City')->where('is_district', false)->first();

        $districts = [];

        // Manila Districts (6)
        if ($manila) {
            $districts[] = ['region_id' => $ncr->id, 'parent_city_id' => $manila->id, 'name' => 'First District', 'type' => 'city', 'is_district' => true, 'psgc_code' => '133901000'];
            $districts[] = ['region_id' => $ncr->id, 'parent_city_id' => $manila->id, 'name' => 'Second District', 'type' => 'city', 'is_district' => true, 'psgc_code' => '133902000'];
            $districts[] = ['region_id' => $ncr->id, 'parent_city_id' => $manila->id, 'name' => 'Third District', 'type' => 'city', 'is_district' => true, 'psgc_code' => '133903000'];
            $districts[] = ['region_id' => $ncr->id, 'parent_city_id' => $manila->id, 'name' => 'Fourth District', 'type' => 'city', 'is_district' => true, 'psgc_code' => '133904000'];
            $districts[] = ['region_id' => $ncr->id, 'parent_city_id' => $manila->id, 'name' => 'Fifth District', 'type' => 'city', 'is_district' => true, 'psgc_code' => '133905000'];
            $districts[] = ['region_id' => $ncr->id, 'parent_city_id' => $manila->id, 'name' => 'Sixth District', 'type' => 'city', 'is_district' => true, 'psgc_code' => '133906000'];
        }

        // Quezon City Districts (6)
        if ($quezonCity) {
            $districts[] = ['region_id' => $ncr->id, 'parent_city_id' => $quezonCity->id, 'name' => 'First District', 'type' => 'city', 'is_district' => true, 'psgc_code' => '137401000'];
            $districts[] = ['region_id' => $ncr->id, 'parent_city_id' => $quezonCity->id, 'name' => 'Second District', 'type' => 'city', 'is_district' => true, 'psgc_code' => '137402000'];
            $districts[] = ['region_id' => $ncr->id, 'parent_city_id' => $quezonCity->id, 'name' => 'Third District', 'type' => 'city', 'is_district' => true, 'psgc_code' => '137403000'];
            $districts[] = ['region_id' => $ncr->id, 'parent_city_id' => $quezonCity->id, 'name' => 'Fourth District', 'type' => 'city', 'is_district' => true, 'psgc_code' => '137404000'];
            $districts[] = ['region_id' => $ncr->id, 'parent_city_id' => $quezonCity->id, 'name' => 'Fifth District', 'type' => 'city', 'is_district' => true, 'psgc_code' => '137405000'];
            $districts[] = ['region_id' => $ncr->id, 'parent_city_id' => $quezonCity->id, 'name' => 'Sixth District', 'type' => 'city', 'is_district' => true, 'psgc_code' => '137406000'];
        }

        // Caloocan Districts (3)
        if ($caloocan) {
            $districts[] = ['region_id' => $ncr->id, 'parent_city_id' => $caloocan->id, 'name' => 'First District', 'type' => 'city', 'is_district' => true, 'psgc_code' => '137201000'];
            $districts[] = ['region_id' => $ncr->id, 'parent_city_id' => $caloocan->id, 'name' => 'Second District', 'type' => 'city', 'is_district' => true, 'psgc_code' => '137202000'];
            $districts[] = ['region_id' => $ncr->id, 'parent_city_id' => $caloocan->id, 'name' => 'Third District', 'type' => 'city', 'is_district' => true, 'psgc_code' => '137203000'];
        }

        // Pasig Districts (2 councilor districts)
        if ($pasig) {
            $districts[] = ['region_id' => $ncr->id, 'parent_city_id' => $pasig->id, 'name' => 'First District', 'type' => 'city', 'is_district' => true, 'psgc_code' => '137701000'];
            $districts[] = ['region_id' => $ncr->id, 'parent_city_id' => $pasig->id, 'name' => 'Second District', 'type' => 'city', 'is_district' => true, 'psgc_code' => '137702000'];
        }

        // Cebu City Districts (2)
        if ($cebuCity && $region7) {
            $districts[] = ['region_id' => $region7->id, 'parent_city_id' => $cebuCity->id, 'name' => 'First District (North)', 'type' => 'city', 'is_district' => true, 'psgc_code' => '072201000'];
            $districts[] = ['region_id' => $region7->id, 'parent_city_id' => $cebuCity->id, 'name' => 'Second District (South)', 'type' => 'city', 'is_district' => true, 'psgc_code' => '072202000'];
        }

        // Davao City Districts (3)
        if ($davaoCity && $region11) {
            $districts[] = ['region_id' => $region11->id, 'parent_city_id' => $davaoCity->id, 'name' => 'First District', 'type' => 'city', 'is_district' => true, 'psgc_code' => '112401000'];
            $districts[] = ['region_id' => $region11->id, 'parent_city_id' => $davaoCity->id, 'name' => 'Second District', 'type' => 'city', 'is_district' => true, 'psgc_code' => '112402000'];
            $districts[] = ['region_id' => $region11->id, 'parent_city_id' => $davaoCity->id, 'name' => 'Third District', 'type' => 'city', 'is_district' => true, 'psgc_code' => '112403000'];
        }

        // Insert all districts
        foreach ($districts as $district) {
            City::create($district);
        }

        $this->command->info('Districts seeded successfully! Added ' . count($districts) . ' districts.');
    }
}
