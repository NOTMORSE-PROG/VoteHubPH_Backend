<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->string('id')->primary(); // CUID from Prisma
            $table->string('email')->unique();
            $table->string('name')->nullable();
            $table->string('password')->nullable(); // Nullable for OAuth users
            $table->timestamp('email_verified_at')->nullable();
            $table->string('image')->nullable();

            // OAuth/SSO fields
            $table->string('provider')->nullable(); // 'google', 'facebook', 'credentials'
            $table->string('provider_id')->nullable();

            // User preferences
            $table->string('language')->default('en');
            $table->string('region')->nullable();
            $table->string('city')->nullable();
            $table->string('barangay')->nullable();
            $table->boolean('profile_completed')->default(false);

            // Metadata
            $table->timestamps(); // created_at, updated_at
            $table->timestamp('last_login_at')->nullable();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary(); // CUID
            $table->string('session_token')->unique();
            $table->string('user_id');
            $table->timestamp('expires');

            // Foreign key to users table
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
