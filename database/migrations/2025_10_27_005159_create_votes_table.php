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
        Schema::create('votes', function (Blueprint $table) {
            $table->string('id')->primary(); // CUID

            // Voter
            $table->string('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            // Candidate
            $table->string('candidate_id');
            // Note: candidate FK will be added after candidates table is created

            // Vote details
            $table->boolean('is_anonymous')->default(false);
            $table->string('vote_type')->default('SUPPORT'); // SUPPORT, AGAINST, NEUTRAL

            $table->timestamp('created_at')->useCurrent();

            // Indexes
            $table->index('user_id');
            $table->index('candidate_id');
            $table->unique(['user_id', 'candidate_id']); // One vote per user per candidate
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('votes');
    }
};
