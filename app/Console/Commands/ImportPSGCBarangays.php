<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\City;
use App\Models\Barangay;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ImportPSGCBarangays extends Command
{
    protected $signature = 'psgc:import-barangays';
    protected $description = 'Import official PSGC barangay data';

    public function handle()
    {
        $this->info('Starting PSGC Barangay Import...');

        if (!Storage::exists('barangay.json') || !Storage::exists('muncity.json')) {
            $this->error('JSON files not found!');
            return 1;
        }

        $this->info('Loading JSON files...');
        $barangays = json_decode(Storage::get('barangay.json'), true);
        $muncities = json_decode(Storage::get('muncity.json'), true);

        $muncityMap = collect($muncities)->pluck('code', 'muncity_id')->toArray();
        $this->info('Creating city mapping...');
        $cities = City::all()->pluck('id', 'psgc_code')->toArray();

        $this->info('Clearing existing data...');
        Barangay::truncate();

        $this->info('Importing ' . count($barangays) . ' barangays...');
        $bar = $this->output->createProgressBar(count($barangays));

        $batch = [];
        $inserted = 0;
        $skipped = 0;

        foreach ($barangays as $brgy) {
            $muncityId = $brgy['muncity_id'];
            $cityPsgcCode = $muncityMap[$muncityId] ?? null;

            if (!$cityPsgcCode || !isset($cities[$cityPsgcCode])) {
                $skipped++;
                $bar->advance();
                continue;
            }

            $batch[] = [
                'city_id' => $cities[$cityPsgcCode],
                'name' => $brgy['description'],
                'psgc_code' => $brgy['code'],
                'created_at' => now(),
                'updated_at' => now(),
            ];

            if (count($batch) >= 500) {
                DB::table('barangays')->insert($batch);
                $inserted += count($batch);
                $batch = [];
            }

            $bar->advance();
        }

        if (!empty($batch)) {
            DB::table('barangays')->insert($batch);
            $inserted += count($batch);
        }

        $bar->finish();
        $this->newLine(2);
        $this->info("Inserted: {$inserted}, Skipped: {$skipped}");

        return 0;
    }
}
