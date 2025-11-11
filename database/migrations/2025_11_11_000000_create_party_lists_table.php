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
        Schema::create('party_lists', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('acronym')->nullable();
            $table->text('description')->nullable();
            $table->string('sector')->nullable(); // e.g., "Labor", "Youth", "Women", etc.
            $table->json('platform')->nullable(); // Array of platform points
            $table->string('logo_url')->nullable();
            $table->string('website')->nullable();
            $table->string('email')->nullable();
            $table->json('social_media')->nullable();
            $table->integer('votes')->default(0);
            $table->integer('member_count')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index('name');
            $table->index('sector');
            $table->index('is_active');
        });

        Schema::create('party_list_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('party_list_id')->constrained('party_lists')->onDelete('cascade');
            $table->foreignId('post_id')->constrained('posts')->onDelete('cascade');
            $table->integer('position_order')->default(0); // For ranking/ordering
            $table->timestamps();
            
            $table->index('party_list_id');
            $table->index('post_id');
            $table->index('position_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('party_list_members');
        Schema::dropIfExists('party_lists');
    }
};

