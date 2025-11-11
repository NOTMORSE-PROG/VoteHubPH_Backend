<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\City;
use App\Models\Barangay;

class ManilaBarangaysSeeder extends Seeder
{
    /**
     * Seed Manila's 897 numbered barangays across its 6 districts
     */
    public function run(): void
    {
        // Get Manila city and its districts
        $manila = City::where('name', 'Manila')->where('is_district', false)->first();

        if (!$manila) {
            $this->command->error('Manila city not found!');
            return;
        }

        $districts = City::where('parent_city_id', $manila->id)
            ->where('is_district', true)
            ->orderBy('name')
            ->get();

        if ($districts->count() === 0) {
            $this->command->error('Manila districts not found! Run DistrictsSeeder first.');
            return;
        }

        $barangays = [];

        // Manila has 897 barangays numbered 1-897
        // Distribute them across the 6 districts based on the actual distribution
        // Reference: https://en.wikipedia.org/wiki/Legislative_districts_of_Manila

        $districtRanges = [
            'First District' => [1, 146],      // Barangays 1-146 (Tondo)
            'Second District' => [147, 267],   // Barangays 147-267 (Binondo area)
            'Third District' => [268, 394],    // Barangays 268-394 (Quiapo, Santa Cruz)
            'Fourth District' => [395, 586],   // Barangays 395-586 (Sampaloc)
            'Fifth District' => [649, 828],    // Barangays 649-828 (Ermita, Malate, Paco south)
            'Sixth District' => [[587, 648], [829, 897]], // Barangays 587-648, 829-897 (Paco north, Pandacan, Santa Ana)
        ];

        foreach ($districts as $district) {
            if (!isset($districtRanges[$district->name])) {
                continue;
            }

            $ranges = $districtRanges[$district->name];

            // Handle Sixth District which has two ranges
            if ($district->name === 'Sixth District') {
                foreach ($ranges as $range) {
                    for ($i = $range[0]; $i <= $range[1]; $i++) {
                        $barangays[] = [
                            'city_id' => $district->id, // Use district ID, not Manila ID
                            'name' => sprintf('%03d', $i), // Format as 001, 002, etc.
                            'psgc_code' => '1339' . sprintf('%05d', $i),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                }
            } else {
                for ($i = $ranges[0]; $i <= $ranges[1]; $i++) {
                    $barangays[] = [
                        'city_id' => $district->id, // Use district ID, not Manila ID
                        'name' => sprintf('%03d', $i), // Format as 001, 002, etc.
                        'psgc_code' => '1339' . sprintf('%05d', $i),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
        }

        // Insert in chunks to avoid memory issues
        foreach (array_chunk($barangays, 100) as $chunk) {
            Barangay::insert($chunk);
        }

        $this->command->info('Successfully seeded ' . count($barangays) . ' Manila barangays across 6 districts!');
    }
}
