<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('episode_watches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('show_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('episode_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('watched_at')->nullable()->index();
            $table->unsignedInteger('runtime')->default(0);
            $table->string('source')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'watched_at']);
            $table->index(['user_id', 'show_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('episode_watches');
    }
};
