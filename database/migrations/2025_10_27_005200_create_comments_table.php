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
        Schema::create('comments', function (Blueprint $table) {
            $table->string('id')->primary(); // CUID
            $table->text('content');

            // Author
            $table->string('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            // Target candidate
            $table->string('candidate_id');
            // Note: candidate FK will be added after candidates table is created

            // Engagement
            $table->integer('likes')->default(0);
            $table->boolean('is_anonymous')->default(false);

            // Timestamps
            $table->timestamps(); // created_at, updated_at

            // Indexes
            $table->index('user_id');
            $table->index('candidate_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};
