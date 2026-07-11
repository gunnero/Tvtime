<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media_lists', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name', 120);
            $table->text('description')->nullable();
            $table->string('visibility', 16)->default('private');
            $table->timestamps();

            $table->index(['user_id', 'name']);
        });

        Schema::create('media_list_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('media_list_id')->constrained()->cascadeOnDelete();
            $table->string('media_type', 16);
            $table->unsignedBigInteger('media_id');
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();

            $table->unique(['media_list_id', 'media_type', 'media_id']);
            $table->index(['user_id', 'media_type', 'media_id']);
            $table->index(['media_list_id', 'position']);
        });

        Schema::create('notification_preferences', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->boolean('new_episodes')->default(true);
            $table->boolean('movie_releases')->default(true);
            $table->boolean('reminders')->default(true);
            $table->boolean('in_app_enabled')->default(true);
            $table->boolean('email_enabled')->default(false);
            $table->timestamps();
        });

        Schema::table('alerts', function (Blueprint $table): void {
            $table->string('dedupe_key', 160)->nullable()->after('category');
            $table->unique(['user_id', 'dedupe_key']);
        });
    }

    public function down(): void
    {
        Schema::table('alerts', function (Blueprint $table): void {
            $table->dropUnique(['user_id', 'dedupe_key']);
            $table->dropColumn('dedupe_key');
        });

        Schema::dropIfExists('notification_preferences');
        Schema::dropIfExists('media_list_items');
        Schema::dropIfExists('media_lists');
    }
};
