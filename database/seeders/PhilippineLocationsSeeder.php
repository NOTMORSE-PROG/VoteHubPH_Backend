<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Region;
use App\Models\Province;
use App\Models\City;
use App\Models\Barangay;

class PhilippineLocationsSeeder extends Seeder
{
    public function run(): void
    {
        // Only run this PostgreSQL-specific command if using pgsql
        if (config('database.default') === 'pgsql') {
            \DB::statement('SET CONSTRAINTS ALL DEFERRED');
        }

        Barangay::truncate();
        City::truncate();
        Province::truncate();
        Region::truncate();

        $regions = [
            ['code' => 'NCR', 'name' => 'National Capital Region (NCR)', 'psgc_code' => '130000000'],
            ['code' => 'CAR', 'name' => 'Cordillera Administrative Region (CAR)', 'psgc_code' => '140000000'],
            ['code' => 'I', 'name' => 'Ilocos Region (Region I)', 'psgc_code' => '010000000'],
            ['code' => 'II', 'name' => 'Cagayan Valley (Region II)', 'psgc_code' => '020000000'],
            ['code' => 'III', 'name' => 'Central Luzon (Region III)', 'psgc_code' => '030000000'],
            ['code' => 'IV-A', 'name' => 'CALABARZON (Region IV-A)', 'psgc_code' => '040000000'],
            ['code' => 'IV-B', 'name' => 'MIMAROPA (Region IV-B)', 'psgc_code' => '170000000'],
            ['code' => 'V', 'name' => 'Bicol Region (Region V)', 'psgc_code' => '050000000'],
            ['code' => 'VI', 'name' => 'Western Visayas (Region VI)', 'psgc_code' => '060000000'],
            ['code' => 'VII', 'name' => 'Central Visayas (Region VII)', 'psgc_code' => '070000000'],
            ['code' => 'VIII', 'name' => 'Eastern Visayas (Region VIII)', 'psgc_code' => '080000000'],
            ['code' => 'IX', 'name' => 'Zamboanga Peninsula (Region IX)', 'psgc_code' => '090000000'],
            ['code' => 'X', 'name' => 'Northern Mindanao (Region X)', 'psgc_code' => '100000000'],
            ['code' => 'XI', 'name' => 'Davao Region (Region XI)', 'psgc_code' => '110000000'],
            ['code' => 'XII', 'name' => 'SOCCSKSARGEN (Region XII)', 'psgc_code' => '120000000'],
            ['code' => 'XIII', 'name' => 'Caraga (Region XIII)', 'psgc_code' => '160000000'],
            ['code' => 'BARMM', 'name' => 'Bangsamoro Autonomous Region in Muslim Mindanao (BARMM)', 'psgc_code' => '150000000'],
        ];

        foreach ($regions as $regionData) {
            Region::create($regionData);
        }

        $ncr = Region::where('code', 'NCR')->first();
        $car = Region::where('code', 'CAR')->first();
        $region1 = Region::where('code', 'I')->first();
        $region2 = Region::where('code', 'II')->first();
        $region3 = Region::where('code', 'III')->first();
        $region4a = Region::where('code', 'IV-A')->first();
        $region4b = Region::where('code', 'IV-B')->first();
        $region5 = Region::where('code', 'V')->first();
        $region6 = Region::where('code', 'VI')->first();
        $region7 = Region::where('code', 'VII')->first();
        $region8 = Region::where('code', 'VIII')->first();
        $region9 = Region::where('code', 'IX')->first();
        $region10 = Region::where('code', 'X')->first();
        $region11 = Region::where('code', 'XI')->first();
        $region12 = Region::where('code', 'XII')->first();
        $region13 = Region::where('code', 'XIII')->first();
        $barmm = Region::where('code', 'BARMM')->first();

        $cities = [
            // NCR Cities (17 cities/municipalities)
            ['region_id' => $ncr->id, 'name' => 'Manila', 'type' => 'city', 'psgc_code' => '133900000'],
            ['region_id' => $ncr->id, 'name' => 'Quezon City', 'type' => 'city', 'psgc_code' => '137400000'],
            ['region_id' => $ncr->id, 'name' => 'Makati', 'type' => 'city', 'psgc_code' => '137500000'],
            ['region_id' => $ncr->id, 'name' => 'Pasig', 'type' => 'city', 'psgc_code' => '137700000'],
            ['region_id' => $ncr->id, 'name' => 'Taguig', 'type' => 'city', 'psgc_code' => '137800000'],
            ['region_id' => $ncr->id, 'name' => 'Pasay', 'type' => 'city', 'psgc_code' => '137600000'],
            ['region_id' => $ncr->id, 'name' => 'Caloocan', 'type' => 'city', 'psgc_code' => '137200000'],
            ['region_id' => $ncr->id, 'name' => 'Marikina', 'type' => 'city', 'psgc_code' => '137300000'],
            ['region_id' => $ncr->id, 'name' => 'Parañaque', 'type' => 'city', 'psgc_code' => '137100000'],
            ['region_id' => $ncr->id, 'name' => 'Las Piñas', 'type' => 'city', 'psgc_code' => '137600000'],
            ['region_id' => $ncr->id, 'name' => 'Muntinlupa', 'type' => 'city', 'psgc_code' => '137900000'],
            ['region_id' => $ncr->id, 'name' => 'Valenzuela', 'type' => 'city', 'psgc_code' => '137500000'],
            ['region_id' => $ncr->id, 'name' => 'Malabon', 'type' => 'city', 'psgc_code' => '137400000'],
            ['region_id' => $ncr->id, 'name' => 'Navotas', 'type' => 'city', 'psgc_code' => '137600000'],
            ['region_id' => $ncr->id, 'name' => 'San Juan', 'type' => 'city', 'psgc_code' => '137700000'],
            ['region_id' => $ncr->id, 'name' => 'Mandaluyong', 'type' => 'city', 'psgc_code' => '137500000'],
            ['region_id' => $ncr->id, 'name' => 'Pateros', 'type' => 'municipality', 'psgc_code' => '137600000'],

            // CAR - Cordillera Administrative Region
            ['region_id' => $car->id, 'name' => 'Baguio', 'type' => 'city', 'psgc_code' => '141100000'],
            ['region_id' => $car->id, 'name' => 'Tabuk', 'type' => 'city', 'psgc_code' => '141400000'],

            // Region I - Ilocos Region
            ['region_id' => $region1->id, 'name' => 'Laoag', 'type' => 'city', 'psgc_code' => '012800000'],
            ['region_id' => $region1->id, 'name' => 'Vigan', 'type' => 'city', 'psgc_code' => '012900000'],
            ['region_id' => $region1->id, 'name' => 'Dagupan', 'type' => 'city', 'psgc_code' => '015500000'],
            ['region_id' => $region1->id, 'name' => 'San Fernando', 'type' => 'city', 'psgc_code' => '015400000'],
            ['region_id' => $region1->id, 'name' => 'Alaminos', 'type' => 'city', 'psgc_code' => '015700000'],

            // Region II - Cagayan Valley
            ['region_id' => $region2->id, 'name' => 'Tuguegarao', 'type' => 'city', 'psgc_code' => '021500000'],
            ['region_id' => $region2->id, 'name' => 'Ilagan', 'type' => 'city', 'psgc_code' => '023100000'],
            ['region_id' => $region2->id, 'name' => 'Santiago', 'type' => 'city', 'psgc_code' => '023200000'],
            ['region_id' => $region2->id, 'name' => 'Cauayan', 'type' => 'city', 'psgc_code' => '023300000'],

            // Region III - Central Luzon
            ['region_id' => $region3->id, 'name' => 'Angeles', 'type' => 'city', 'psgc_code' => '034100000'],
            ['region_id' => $region3->id, 'name' => 'Olongapo', 'type' => 'city', 'psgc_code' => '037100000'],
            ['region_id' => $region3->id, 'name' => 'San Fernando', 'type' => 'city', 'psgc_code' => '034500000'],
            ['region_id' => $region3->id, 'name' => 'Mabalacat', 'type' => 'city', 'psgc_code' => '034900000'],
            ['region_id' => $region3->id, 'name' => 'Tarlac City', 'type' => 'city', 'psgc_code' => '036900000'],
            ['region_id' => $region3->id, 'name' => 'Cabanatuan', 'type' => 'city', 'psgc_code' => '034900000'],

            // Region IV-A - CALABARZON
            ['region_id' => $region4a->id, 'name' => 'Antipolo', 'type' => 'city', 'psgc_code' => '045800000'],
            ['region_id' => $region4a->id, 'name' => 'Calamba', 'type' => 'city', 'psgc_code' => '043400000'],
            ['region_id' => $region4a->id, 'name' => 'Lucena', 'type' => 'city', 'psgc_code' => '045600000'],
            ['region_id' => $region4a->id, 'name' => 'Batangas City', 'type' => 'city', 'psgc_code' => '041000000'],
            ['region_id' => $region4a->id, 'name' => 'Lipa', 'type' => 'city', 'psgc_code' => '041100000'],
            ['region_id' => $region4a->id, 'name' => 'Cavite City', 'type' => 'city', 'psgc_code' => '042100000'],
            ['region_id' => $region4a->id, 'name' => 'Bacoor', 'type' => 'city', 'psgc_code' => '042200000'],
            ['region_id' => $region4a->id, 'name' => 'Dasmariñas', 'type' => 'city', 'psgc_code' => '042300000'],

            // Region IV-B - MIMAROPA
            ['region_id' => $region4b->id, 'name' => 'Puerto Princesa', 'type' => 'city', 'psgc_code' => '175300000'],
            ['region_id' => $region4b->id, 'name' => 'Calapan', 'type' => 'city', 'psgc_code' => '175200000'],

            // Region V - Bicol
            ['region_id' => $region5->id, 'name' => 'Legazpi', 'type' => 'city', 'psgc_code' => '050500000'],
            ['region_id' => $region5->id, 'name' => 'Naga', 'type' => 'city', 'psgc_code' => '051600000'],
            ['region_id' => $region5->id, 'name' => 'Iriga', 'type' => 'city', 'psgc_code' => '051700000'],
            ['region_id' => $region5->id, 'name' => 'Ligao', 'type' => 'city', 'psgc_code' => '050800000'],
            ['region_id' => $region5->id, 'name' => 'Tabaco', 'type' => 'city', 'psgc_code' => '050900000'],

            // Region VI - Western Visayas
            ['region_id' => $region6->id, 'name' => 'Iloilo City', 'type' => 'city', 'psgc_code' => '063000000'],
            ['region_id' => $region6->id, 'name' => 'Bacolod', 'type' => 'city', 'psgc_code' => '064500000'],
            ['region_id' => $region6->id, 'name' => 'Roxas', 'type' => 'city', 'psgc_code' => '061900000'],
            ['region_id' => $region6->id, 'name' => 'Kalibo', 'type' => 'municipality', 'psgc_code' => '060300000'],

            // Region VII - Central Visayas
            ['region_id' => $region7->id, 'name' => 'Cebu City', 'type' => 'city', 'psgc_code' => '072200000'],
            ['region_id' => $region7->id, 'name' => 'Mandaue', 'type' => 'city', 'psgc_code' => '072300000'],
            ['region_id' => $region7->id, 'name' => 'Lapu-Lapu', 'type' => 'city', 'psgc_code' => '072400000'],
            ['region_id' => $region7->id, 'name' => 'Tagbilaran', 'type' => 'city', 'psgc_code' => '071200000'],
            ['region_id' => $region7->id, 'name' => 'Dumaguete', 'type' => 'city', 'psgc_code' => '074600000'],

            // Region VIII - Eastern Visayas
            ['region_id' => $region8->id, 'name' => 'Tacloban', 'type' => 'city', 'psgc_code' => '083700000'],
            ['region_id' => $region8->id, 'name' => 'Ormoc', 'type' => 'city', 'psgc_code' => '083800000'],
            ['region_id' => $region8->id, 'name' => 'Calbayog', 'type' => 'city', 'psgc_code' => '086000000'],
            ['region_id' => $region8->id, 'name' => 'Catbalogan', 'type' => 'city', 'psgc_code' => '086100000'],
            ['region_id' => $region8->id, 'name' => 'Borongan', 'type' => 'city', 'psgc_code' => '082600000'],

            // Region IX - Zamboanga Peninsula
            ['region_id' => $region9->id, 'name' => 'Zamboanga City', 'type' => 'city', 'psgc_code' => '097200000'],
            ['region_id' => $region9->id, 'name' => 'Pagadian', 'type' => 'city', 'psgc_code' => '097300000'],
            ['region_id' => $region9->id, 'name' => 'Dipolog', 'type' => 'city', 'psgc_code' => '097100000'],
            ['region_id' => $region9->id, 'name' => 'Isabela', 'type' => 'city', 'psgc_code' => '090900000'],

            // Region X - Northern Mindanao
            ['region_id' => $region10->id, 'name' => 'Cagayan de Oro', 'type' => 'city', 'psgc_code' => '104200000'],
            ['region_id' => $region10->id, 'name' => 'Iligan', 'type' => 'city', 'psgc_code' => '103500000'],
            ['region_id' => $region10->id, 'name' => 'Valencia', 'type' => 'city', 'psgc_code' => '104300000'],
            ['region_id' => $region10->id, 'name' => 'Ozamiz', 'type' => 'city', 'psgc_code' => '104600000'],
            ['region_id' => $region10->id, 'name' => 'Oroquieta', 'type' => 'city', 'psgc_code' => '104700000'],

            // Region XI - Davao Region
            ['region_id' => $region11->id, 'name' => 'Davao City', 'type' => 'city', 'psgc_code' => '112400000'],
            ['region_id' => $region11->id, 'name' => 'Tagum', 'type' => 'city', 'psgc_code' => '112300000'],
            ['region_id' => $region11->id, 'name' => 'Digos', 'type' => 'city', 'psgc_code' => '112500000'],
            ['region_id' => $region11->id, 'name' => 'Mati', 'type' => 'city', 'psgc_code' => '118200000'],

            // Region XII - SOCCSKSARGEN
            ['region_id' => $region12->id, 'name' => 'General Santos', 'type' => 'city', 'psgc_code' => '126300000'],
            ['region_id' => $region12->id, 'name' => 'Koronadal', 'type' => 'city', 'psgc_code' => '126300000'],
            ['region_id' => $region12->id, 'name' => 'Tacurong', 'type' => 'city', 'psgc_code' => '126800000'],
            ['region_id' => $region12->id, 'name' => 'Kidapawan', 'type' => 'city', 'psgc_code' => '124700000'],

            // Region XIII - Caraga
            ['region_id' => $region13->id, 'name' => 'Butuan', 'type' => 'city', 'psgc_code' => '160200000'],
            ['region_id' => $region13->id, 'name' => 'Surigao', 'type' => 'city', 'psgc_code' => '168500000'],
            ['region_id' => $region13->id, 'name' => 'Bislig', 'type' => 'city', 'psgc_code' => '168200000'],
            ['region_id' => $region13->id, 'name' => 'Bayugan', 'type' => 'city', 'psgc_code' => '160300000'],

            // BARMM - Bangsamoro Autonomous Region
            ['region_id' => $barmm->id, 'name' => 'Cotabato City', 'type' => 'city', 'psgc_code' => '153800000'],
            ['region_id' => $barmm->id, 'name' => 'Marawi', 'type' => 'city', 'psgc_code' => '153600000'],
        ];

        foreach ($cities as $cityData) {
            City::create($cityData);
        }

        // Barangay data is NOT seeded
        // Complete barangay data requires the official PSGC database with 42,000+ barangays
        // Users can manually enter their barangay using the "My barangay is not listed" checkbox


        $this->command->info('Philippine locations seeded successfully!');
        $this->command->info('Total: ' . Region::count() . ' regions, ' . City::count() . ' cities, ' . Barangay::count() . ' barangays');
    }
}