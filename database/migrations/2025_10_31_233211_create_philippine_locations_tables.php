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
        // Regions table
        Schema::create('regions', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique(); // NCR, CAR, I, II, etc.
            $table->string('name');
            $table->string('psgc_code', 20)->nullable(); // Philippine Standard Geographic Code
            $table->timestamps();
        });

        // Provinces table
        Schema::create('provinces', function (Blueprint $table) {
            $table->id();
            $table->foreignId('region_id')->constrained('regions')->onDelete('cascade');
            $table->string('name');
            $table->string('psgc_code', 20)->nullable();
            $table->timestamps();
        });

        // Cities/Municipalities table
        Schema::create('cities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('region_id')->constrained('regions')->onDelete('cascade');
            $table->foreignId('province_id')->nullable()->constrained('provinces')->onDelete('cascade');
            $table->string('name');
            $table->enum('type', ['city', 'municipality'])->default('city');
            $table->string('psgc_code', 20)->nullable();
            $table->timestamps();
        });

        // Barangays table
        Schema::create('barangays', function (Blueprint $table) {
            $table->id();
            $table->foreignId('city_id')->constrained('cities')->onDelete('cascade');
            $table->string('name');
            $table->string('psgc_code', 20)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('barangays');
        Schema::dropIfExists('cities');
        Schema::dropIfExists('provinces');
        Schema::dropIfExists('regions');
    }
};
