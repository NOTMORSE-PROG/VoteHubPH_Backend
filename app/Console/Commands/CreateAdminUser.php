<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreateAdminUser extends Command
{
    protected $signature = 'admin:create {--email=admin@votehubph} {--password=admin}';
    protected $description = 'Create an admin user';

    public function handle()
    {
        $email = $this->option('email');
        $password = $this->option('password');

        // Check if admin user already exists
        $existingAdmin = User::where('email', $email)->first();
        
        if ($existingAdmin) {
            if ($this->confirm("Admin user with email {$email} already exists. Do you want to update it to admin?", true)) {
                // Update existing user to admin
                DB::table('User')
                    ->where('id', $existingAdmin->id)
                    ->update([
                        'is_admin' => true,
                        'password' => Hash::make($password),
                        'updatedAt' => now(),
                    ]);
                
                $this->info("Admin user updated successfully!");
                $this->info("Email: {$email}");
                $this->info("Password: {$password}");
                return 0;
            } else {
                $this->info("Operation cancelled.");
                return 0;
            }
        }

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

        $this->info("Admin user created successfully!");
        $this->info("Email: {$email}");
        $this->info("Password: {$password}");
        $this->warn("Please change the password after first login!");

        return 0;
    }
}
