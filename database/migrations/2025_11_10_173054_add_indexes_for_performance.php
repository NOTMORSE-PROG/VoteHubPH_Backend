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
            $table->index('status'); // For filtering approved posts
            $table->index('created_at'); // For ordering
            $table->index(['status', 'created_at']); // Compound index for common query
        });

        Schema::table('votes', function (Blueprint $table) {
            // post_id and user_id unique index already exists, but add post_id for counting
            if (!Schema::hasIndex('votes', ['post_id'])) {
                $table->index('post_id');
            }
        });

        Schema::table('comments', function (Blueprint $table) {
            // parent_id index already added, ensure post_id is indexed
            if (!Schema::hasIndex('comments', ['post_id'])) {
                $table->index('post_id');
            }
            $table->index(['post_id', 'parent_id']); // Compound index for fetching top-level comments
        });

        Schema::table('comment_likes', function (Blueprint $table) {
            if (!Schema::hasIndex('comment_likes', ['comment_id'])) {
                $table->index('comment_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['status', 'created_at']);
        });

        Schema::table('votes', function (Blueprint $table) {
            if (Schema::hasIndex('votes', ['post_id'])) {
                $table->dropIndex(['post_id']);
            }
        });

        Schema::table('comments', function (Blueprint $table) {
            if (Schema::hasIndex('comments', ['post_id'])) {
                $table->dropIndex(['post_id']);
            }
            $table->dropIndex(['post_id', 'parent_id']);
        });

        Schema::table('comment_likes', function (Blueprint $table) {
            if (Schema::hasIndex('comment_likes', ['comment_id'])) {
                $table->dropIndex(['comment_id']);
            }
        });
    }
};
