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
        // Create User table for NextAuth (capital U to match Prisma schema)
        Schema::create('User', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('email')->unique();
            $table->string('name')->nullable();
            $table->string('password')->nullable(); // Nullable for OAuth users
            $table->timestamp('emailVerified')->nullable();
            $table->string('image')->nullable();

            // OAuth/SSO fields
            $table->string('provider')->nullable();
            $table->string('providerId')->nullable();

            // User preferences
            $table->string('language')->default('en');
            $table->string('region')->nullable();
            $table->string('city')->nullable();
            $table->string('barangay')->nullable();
            $table->boolean('profileCompleted')->default(false);

            // Timestamps
            $table->timestamp('createdAt')->useCurrent();
            $table->timestamp('updatedAt')->useCurrent()->useCurrentOnUpdate();
            $table->timestamp('lastLoginAt')->nullable();

            $table->index('email');
            $table->index(['provider', 'providerId']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('User');
    }
};
