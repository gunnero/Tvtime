<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('external_source')->nullable();
            $table->string('external_id')->nullable();
            $table->string('title');
            $table->string('poster_url')->nullable();
            $table->string('fanart_url')->nullable();
            $table->boolean('followed')->default(false)->index();
            $table->unsignedInteger('seen_episodes')->default(0);
            $table->unsignedInteger('aired_episodes')->default(0);
            $table->unsignedInteger('runtime')->default(0);
            $table->timestamp('latest_seen_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'title']);
            $table->unique(['user_id', 'external_source', 'external_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shows');
    }
};
