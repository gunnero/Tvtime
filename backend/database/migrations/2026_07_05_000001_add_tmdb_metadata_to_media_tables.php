<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('movies', function (Blueprint $table): void {
            $table->unsignedBigInteger('tmdb_id')->nullable()->index();
            $table->string('imdb_id')->nullable()->index();
            $table->string('tvdb_id')->nullable()->index();
            $table->string('original_title')->nullable();
            $table->text('overview')->nullable();
            $table->string('poster_path')->nullable();
            $table->string('backdrop_path')->nullable();
            $table->date('release_date')->nullable()->index();
            $table->json('genres')->nullable();
            $table->string('status')->nullable()->index();
            $table->decimal('vote_average', 4, 1)->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('metadata_refreshed_at')->nullable()->index();

            $table->index(['user_id', 'tmdb_id']);
        });

        Schema::table('shows', function (Blueprint $table): void {
            $table->unsignedBigInteger('tmdb_id')->nullable()->index();
            $table->string('imdb_id')->nullable()->index();
            $table->string('tvdb_id')->nullable()->index();
            $table->string('original_title')->nullable();
            $table->text('overview')->nullable();
            $table->string('poster_path')->nullable();
            $table->string('backdrop_path')->nullable();
            $table->date('first_air_date')->nullable()->index();
            $table->json('genres')->nullable();
            $table->string('status')->nullable()->index();
            $table->decimal('vote_average', 4, 1)->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('metadata_refreshed_at')->nullable()->index();

            $table->index(['user_id', 'tmdb_id']);
        });

        Schema::table('episodes', function (Blueprint $table): void {
            $table->unsignedBigInteger('tmdb_id')->nullable()->index();
            $table->string('imdb_id')->nullable()->index();
            $table->string('tvdb_id')->nullable()->index();
            $table->string('original_title')->nullable();
            $table->text('overview')->nullable();
            $table->string('poster_path')->nullable();
            $table->string('backdrop_path')->nullable();
            $table->json('genres')->nullable();
            $table->string('status')->nullable()->index();
            $table->decimal('vote_average', 4, 1)->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('metadata_refreshed_at')->nullable()->index();

            $table->index(['user_id', 'tmdb_id']);
        });
    }

    public function down(): void
    {
        Schema::table('episodes', function (Blueprint $table): void {
            $table->dropIndex(['user_id', 'tmdb_id']);
            $table->dropColumn([
                'tmdb_id',
                'imdb_id',
                'tvdb_id',
                'original_title',
                'overview',
                'poster_path',
                'backdrop_path',
                'genres',
                'status',
                'vote_average',
                'metadata',
                'metadata_refreshed_at',
            ]);
        });

        Schema::table('shows', function (Blueprint $table): void {
            $table->dropIndex(['user_id', 'tmdb_id']);
            $table->dropColumn([
                'tmdb_id',
                'imdb_id',
                'tvdb_id',
                'original_title',
                'overview',
                'poster_path',
                'backdrop_path',
                'first_air_date',
                'genres',
                'status',
                'vote_average',
                'metadata',
                'metadata_refreshed_at',
            ]);
        });

        Schema::table('movies', function (Blueprint $table): void {
            $table->dropIndex(['user_id', 'tmdb_id']);
            $table->dropColumn([
                'tmdb_id',
                'imdb_id',
                'tvdb_id',
                'original_title',
                'overview',
                'poster_path',
                'backdrop_path',
                'release_date',
                'genres',
                'status',
                'vote_average',
                'metadata',
                'metadata_refreshed_at',
            ]);
        });
    }
};
