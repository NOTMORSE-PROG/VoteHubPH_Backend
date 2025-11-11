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
        Schema::table('posts', function (Blueprint $table) {
            $table->unsignedBigInteger('city_id')->nullable()->after('level');
            $table->unsignedBigInteger('barangay_id')->nullable()->after('city_id');
            
            $table->foreign('city_id')->references('id')->on('cities')->onDelete('set null');
            $table->foreign('barangay_id')->references('id')->on('barangays')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropForeign(['city_id']);
            $table->dropForeign(['barangay_id']);
            $table->dropColumn(['city_id', 'barangay_id']);
        });
    }
};
