<?php

namespace App\Console\Commands;

use App\Models\Movie;
use App\Services\MediaMetadataService;
use Illuminate\Console\Command;

class EnrichMovieMetadataCommand extends Command
{
    protected $signature = 'mediahub:enrich-movie {movie_id}';

    protected $description = 'Enrich one movie with optional TMDB metadata.';

    public function handle(MediaMetadataService $metadata): int
    {
        $movie = Movie::find($this->argument('movie_id'));

        if (! $movie) {
            $this->error('Metadata enrichment failed: movie was not found.');

            return self::FAILURE;
        }

        $this->printSummary($metadata->enrichMovie($movie));

        return self::SUCCESS;
    }

    /**
     * @param  array{searched:int,matched:int,enriched:int,skipped:int,failed:int}  $summary
     */
    private function printSummary(array $summary): void
    {
        foreach ($summary as $key => $value) {
            $this->line($key.': '.$value);
        }
    }
}
