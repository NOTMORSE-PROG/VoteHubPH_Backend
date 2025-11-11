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
        Schema::table('User', function (Blueprint $table) {
            $table->boolean('mute_comment_notifications')->default(false)->after('prefer_anonymous_voting');
            $table->boolean('mute_like_notifications')->default(false)->after('mute_comment_notifications');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('User', function (Blueprint $table) {
            $table->dropColumn(['mute_comment_notifications', 'mute_like_notifications']);
        });
    }
};

