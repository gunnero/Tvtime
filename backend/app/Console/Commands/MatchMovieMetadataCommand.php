<?php

namespace App\Console\Commands;

use App\Models\Movie;
use App\Services\MediaMetadataService;
use Illuminate\Console\Command;

class MatchMovieMetadataCommand extends Command
{
    protected $signature = 'mediahub:match-movie
        {movie_id}
        {--tmdb-id= : TMDB movie ID selected during manual review}';

    protected $description = 'Manually match one movie to a TMDB movie ID.';

    public function handle(MediaMetadataService $metadata): int
    {
        $movie = Movie::find($this->argument('movie_id'));

        if (! $movie) {
            $this->error('Manual movie match failed: movie was not found.');

            return self::FAILURE;
        }

        $tmdbId = $this->option('tmdb-id');

        if (! is_numeric($tmdbId) || (int) $tmdbId < 1) {
            $this->error('Manual movie match failed: --tmdb-id must be a positive integer.');

            return self::FAILURE;
        }

        $summary = $metadata->matchMovie($movie, (int) $tmdbId);
        $movie->refresh();

        $this->line('movie_id: '.$movie->id);
        $this->line('tmdb_id: '.($movie->tmdb_id ?: 'none'));
        $this->line('match_method: '.($movie->metadata['match']['method'] ?? 'none'));

        foreach ($summary as $key => $value) {
            $this->line($key.': '.$value);
        }

        return $summary['enriched'] > 0 ? self::SUCCESS : self::FAILURE;
    }
}
