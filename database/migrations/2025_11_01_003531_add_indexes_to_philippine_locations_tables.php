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
        // Add indexes for faster queries
        Schema::table('regions', function (Blueprint $table) {
            $table->index('name');
        });

        Schema::table('cities', function (Blueprint $table) {
            $table->index('name');
            $table->index('region_id');
        });

        Schema::table('barangays', function (Blueprint $table) {
            $table->index('city_id');
            $table->index('psgc_code');
        });

        Schema::table('provinces', function (Blueprint $table) {
            $table->index('name');
            $table->index('region_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('regions', function (Blueprint $table) {
            $table->dropIndex(['name']);
        });

        Schema::table('cities', function (Blueprint $table) {
            $table->dropIndex(['name']);
            $table->dropIndex(['region_id']);
        });

        Schema::table('barangays', function (Blueprint $table) {
            $table->dropIndex(['city_id']);
            $table->dropIndex(['psgc_code']);
        });

        Schema::table('provinces', function (Blueprint $table) {
            $table->dropIndex(['name']);
            $table->dropIndex(['region_id']);
        });
    }
};
