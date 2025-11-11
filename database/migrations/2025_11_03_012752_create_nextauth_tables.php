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
        // Account table for NextAuth OAuth accounts
        Schema::create('Account', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('userId');
            $table->string('type');
            $table->string('provider');
            $table->string('providerAccountId');
            $table->text('refresh_token')->nullable();
            $table->text('access_token')->nullable();
            $table->integer('expires_at')->nullable();
            $table->string('token_type')->nullable();
            $table->string('scope')->nullable();
            $table->text('id_token')->nullable();
            $table->string('session_state')->nullable();

            $table->unique(['provider', 'providerAccountId']);
            $table->index('userId');
            $table->foreign('userId')->references('id')->on('User')->onDelete('cascade');
        });

        // Session table for NextAuth sessions
        Schema::create('Session', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('sessionToken')->unique();
            $table->string('userId');
            $table->timestamp('expires');

            $table->index('userId');
            $table->foreign('userId')->references('id')->on('User')->onDelete('cascade');
        });

        // VerificationToken table for email verification
        Schema::create('VerificationToken', function (Blueprint $table) {
            $table->string('identifier');
            $table->string('token')->unique();
            $table->timestamp('expires');

            $table->unique(['identifier', 'token']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('VerificationToken');
        Schema::dropIfExists('Session');
        Schema::dropIfExists('Account');
    }
};
