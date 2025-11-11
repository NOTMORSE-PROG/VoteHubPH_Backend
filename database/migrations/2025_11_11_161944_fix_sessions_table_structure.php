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
        // Drop the old sessions table if it exists
        Schema::dropIfExists('sessions');
        
        // Create the correct Laravel sessions table structure
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sessions');
        
        // Recreate the old structure if needed
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('session_token')->unique();
            $table->string('user_id');
            $table->timestamp('expires');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }
};
