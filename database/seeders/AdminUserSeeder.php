<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $email = 'admin@votehubph';
        $password = 'admin';

        // Check if admin user already exists
        $existingAdmin = DB::table('User')->where('email', $email)->first();
        
        if ($existingAdmin) {
            // Update existing user to admin
            DB::table('User')
                ->where('id', $existingAdmin->id)
                ->update([
                    'is_admin' => true,
                    'password' => Hash::make($password),
                    'updatedAt' => now(),
                ]);
            
            $this->command->info("Admin user updated successfully!");
        } else {
            // Create new admin user
            $userId = 'c' . Str::random(24);
            
            DB::table('User')->insert([
                'id' => $userId,
                'email' => $email,
                'name' => 'Admin',
                'password' => Hash::make($password),
                'is_admin' => true,
                'provider' => 'credentials',
                'language' => 'en',
                'profile_completed' => true,
                'email_verified_at' => now(),
                'createdAt' => now(),
                'updatedAt' => now(),
            ]);

            $this->command->info("Admin user created successfully!");
        }
        
        $this->command->warn("Admin credentials:");
        $this->command->line("Email: {$email}");
        $this->command->line("Password: {$password}");
        $this->command->warn("⚠️  Please change the password after first login!");
    }
}
