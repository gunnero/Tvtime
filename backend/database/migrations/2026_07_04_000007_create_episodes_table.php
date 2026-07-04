<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('episodes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('show_id')->nullable()->constrained()->nullOnDelete();
            $table->string('external_source')->nullable();
            $table->string('external_id')->nullable();
            $table->unsignedInteger('season_number')->nullable();
            $table->unsignedInteger('episode_number')->nullable();
            $table->string('title')->nullable();
            $table->unsignedInteger('runtime')->default(0);
            $table->date('air_date')->nullable()->index();
            $table->timestamps();

            $table->index(['user_id', 'show_id']);
            $table->index(['user_id', 'air_date']);
            $table->unique(['user_id', 'external_source', 'external_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('episodes');
    }
};
