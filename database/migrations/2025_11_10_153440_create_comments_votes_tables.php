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
        // Drop existing tables if they exist
        Schema::dropIfExists('comment_likes');
        Schema::dropIfExists('votes');
        Schema::dropIfExists('comments');

        // Comments table
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained('posts')->onDelete('cascade');
            $table->string('user_id');
            $table->foreign('user_id')->references('id')->on('User')->onDelete('cascade');
            $table->text('content');
            $table->boolean('is_anonymous')->default(false);
            $table->integer('likes_count')->default(0);
            $table->timestamps();

            $table->index(['post_id', 'created_at']);
        });

        // Votes table
        Schema::create('votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained('posts')->onDelete('cascade');
            $table->string('user_id');
            $table->foreign('user_id')->references('id')->on('User')->onDelete('cascade');
            $table->boolean('is_anonymous')->default(false);
            $table->timestamps();

            // One vote per user per post
            $table->unique(['post_id', 'user_id']);
            $table->index('post_id');
        });

        // Comment likes table
        Schema::create('comment_likes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('comment_id')->constrained('comments')->onDelete('cascade');
            $table->string('user_id');
            $table->foreign('user_id')->references('id')->on('User')->onDelete('cascade');
            $table->timestamps();

            // One like per user per comment
            $table->unique(['comment_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comment_likes');
        Schema::dropIfExists('votes');
        Schema::dropIfExists('comments');
    }
};
