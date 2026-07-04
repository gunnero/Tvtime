<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('movies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('external_source')->nullable();
            $table->string('external_id')->nullable();
            $table->string('title');
            $table->string('poster_url')->nullable();
            $table->unsignedInteger('runtime')->default(0);
            $table->boolean('is_to_watch')->default(false)->index();
            $table->timestamps();

            $table->index(['user_id', 'title']);
            $table->unique(['user_id', 'external_source', 'external_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movies');
    }
};
