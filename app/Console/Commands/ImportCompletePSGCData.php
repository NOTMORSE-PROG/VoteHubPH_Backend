<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Region;
use App\Models\City;
use App\Models\Barangay;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ImportCompletePSGCData extends Command
{
    protected $signature = 'psgc:import-all';
    protected $description = 'Import ALL cities, districts, and barangays from PSGC data';

    public function handle()
    {
        $this->info('Starting complete PSGC import...');

        // Check files
        if (!Storage::exists('barangay.json') || !Storage::exists('muncity.json')) {
            $this->error('JSON files not found in storage/app/private/');
            return 1;
        }

        // Load JSON data
        $this->info('Loading JSON files...');
        $muncities = json_decode(Storage::get('muncity.json'), true);
        $barangays = json_decode(Storage::get('barangay.json'), true);

        $this->info('Loaded ' . count($muncities) . ' cities and ' . count($barangays) . ' barangays');

        // Get all regions
        $regions = Region::all()->pluck('id', 'psgc_code')->toArray();
        $this->info('Found ' . count($regions) . ' regions in database');

        // Map PSGC code to region based on first 2 digits
        $regionMap = [
            '01' => '010000000', // Region I
            '02' => '020000000', // Region II
            '03' => '030000000', // Region III
            '04' => '040000000', // Region IV-A
            '05' => '050000000', // Region V
            '06' => '060000000', // Region VI
            '07' => '070000000', // Region VII
            '08' => '080000000', // Region VIII
            '09' => '090000000', // Region IX
            '10' => '100000000', // Region X
            '11' => '110000000', // Region XI
            '12' => '120000000', // Region XII
            '13' => '130000000', // NCR
            '14' => '140000000', // CAR
            '15' => '150000000', // BARMM
            '16' => '160000000', // Region XIII (Caraga)
            '17' => '170000000', // Region IV-B (MIMAROPA)
        ];

        // Clear data
        $this->info('Clearing existing cities and barangays...');
        Barangay::truncate();
        City::truncate();

        // FIRST PASS: Import main cities (PSGC ends with 00000)
        $this->info('Importing main cities...');
        $mainCities = collect($muncities)->filter(function($m) {
            return substr((string)$m['code'], -5) === '00000';
        });

        $bar = $this->output->createProgressBar($mainCities->count());

        $cityBatch = [];

        foreach ($mainCities as $muncity) {
            $code = (string)$muncity['code'];
            $prefix = substr($code, 0, 2);

            $regionPsgc = $regionMap[$prefix] ?? null;
            if (!$regionPsgc || !isset($regions[$regionPsgc])) {
                $bar->advance();
                continue;
            }

            $cityBatch[] = [
                'region_id' => $regions[$regionPsgc],
                'province_id' => null,
                'parent_city_id' => null,
                'name' => $muncity['description'],
                'type' => str_contains(strtolower($muncity['description']), 'city') ? 'city' : 'municipality',
                'is_district' => false,
                'psgc_code' => $code,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            if (count($cityBatch) >= 500) {
                DB::table('cities')->insert($cityBatch);
                $cityBatch = [];
            }

            $bar->advance();
        }

        if (!empty($cityBatch)) {
            DB::table('cities')->insert($cityBatch);
        }

        $bar->finish();
        $this->newLine();

        $mainCityCount = City::count();
        $this->info("Imported {$mainCityCount} main cities");

        // Get main cities for district linking
        $this->info('Loading main cities for district linking...');
        $mainCityMap = City::whereNull('parent_city_id')->get()->keyBy(function($city) {
            // Key by first 4 digits of PSGC (city identifier)
            return substr($city->psgc_code, 0, 4);
        });

        // SECOND PASS: Import districts (PSGC doesn't end with 00000)
        $this->info('Importing districts...');
        $districts = collect($muncities)->filter(function($m) {
            return substr((string)$m['code'], -5) !== '00000';
        });

        $bar = $this->output->createProgressBar($districts->count());

        $districtBatch = [];

        foreach ($districts as $muncity) {
            $code = (string)$muncity['code'];
            $prefix = substr($code, 0, 2);
            $cityPrefix = substr($code, 0, 4); // First 4 digits identify parent city

            $regionPsgc = $regionMap[$prefix] ?? null;
            if (!$regionPsgc || !isset($regions[$regionPsgc])) {
                $bar->advance();
                continue;
            }

            // Find parent city
            $parentCity = $mainCityMap->get($cityPrefix);

            $districtBatch[] = [
                'region_id' => $regions[$regionPsgc],
                'province_id' => null,
                'parent_city_id' => $parentCity?->id,
                'name' => $muncity['description'],
                'type' => str_contains(strtolower($muncity['description']), 'city') ? 'city' : 'municipality',
                'is_district' => $parentCity ? true : false,
                'psgc_code' => $code,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            if (count($districtBatch) >= 500) {
                DB::table('cities')->insert($districtBatch);
                $districtBatch = [];
            }

            $bar->advance();
        }

        if (!empty($districtBatch)) {
            DB::table('cities')->insert($districtBatch);
        }

        $bar->finish();
        $this->newLine();

        $cityCount = City::count();
        $districtCount = City::where('is_district', true)->count();
        $this->info("Total cities/municipalities: {$cityCount}");
        $this->info("Districts: {$districtCount}");

        // Create mapping of PSGC codes to city IDs for barangay import
        $this->info('Creating city mapping for barangays...');
        $cities = City::all()->pluck('id', 'psgc_code')->toArray();

        // Create muncity code mapping
        $muncityCodeMap = collect($muncities)->pluck('code', 'muncity_id')->toArray();

        // Import barangays
        $this->info('Importing barangays...');
        $bar = $this->output->createProgressBar(count($barangays));

        $barangayBatch = [];
        $inserted = 0;
        $skipped = 0;

        foreach ($barangays as $brgy) {
            $muncityId = $brgy['muncity_id'];
            $cityPsgcCode = (string)($muncityCodeMap[$muncityId] ?? null);

            if (!$cityPsgcCode || !isset($cities[$cityPsgcCode])) {
                $skipped++;
                $bar->advance();
                continue;
            }

            $barangayBatch[] = [
                'city_id' => $cities[$cityPsgcCode],
                'name' => $brgy['description'],
                'psgc_code' => $brgy['code'],
                'created_at' => now(),
                'updated_at' => now(),
            ];

            if (count($barangayBatch) >= 500) {
                DB::table('barangays')->insert($barangayBatch);
                $inserted += count($barangayBatch);
                $barangayBatch = [];
            }

            $bar->advance();
        }

        if (!empty($barangayBatch)) {
            DB::table('barangays')->insert($barangayBatch);
            $inserted += count($barangayBatch);
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("=== IMPORT COMPLETE ===");
        $this->info("Cities imported: {$cityCount}");
        $this->info("Districts: {$districtCount}");
        $this->info("Barangays inserted: {$inserted}");
        $this->info("Barangays skipped: {$skipped}");

        return 0;
    }
}
