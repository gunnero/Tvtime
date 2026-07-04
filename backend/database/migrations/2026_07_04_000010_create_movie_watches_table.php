<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('movie_watches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('movie_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('watched_at')->nullable()->index();
            $table->unsignedInteger('runtime')->default(0);
            $table->unsignedInteger('watch_count')->default(1);
            $table->string('source')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'watched_at']);
            $table->index(['user_id', 'movie_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movie_watches');
    }
};
