<?php

namespace App\Console\Commands;

use App\Models\Episode;
use App\Services\MediaMetadataService;
use Illuminate\Console\Command;

class MatchEpisodeMetadataCommand extends Command
{
    protected $signature = 'mediahub:match-episode
        {episode_id}
        {--tmdb-season= : TMDB season number selected during manual review}
        {--tmdb-episode= : TMDB episode number selected during manual review}';

    protected $description = 'Manually match one episode to a TMDB season and episode.';

    public function handle(MediaMetadataService $metadata): int
    {
        $episode = Episode::find($this->argument('episode_id'));

        if (! $episode) {
            $this->error('Manual episode match failed: episode was not found.');

            return self::FAILURE;
        }

        $season = $this->option('tmdb-season');
        $episodeNumber = $this->option('tmdb-episode');

        if (! is_numeric($season) || (int) $season < 1) {
            $this->error('Manual episode match failed: --tmdb-season must be a positive integer.');

            return self::FAILURE;
        }

        if (! is_numeric($episodeNumber) || (int) $episodeNumber < 1) {
            $this->error('Manual episode match failed: --tmdb-episode must be a positive integer.');

            return self::FAILURE;
        }

        $summary = $metadata->matchEpisode($episode, (int) $season, (int) $episodeNumber);
        $episode->refresh();

        $this->line('episode_id: '.$episode->id);
        $this->line('tmdb_id: '.($episode->tmdb_id ?: 'none'));
        $this->line('match_method: '.($episode->metadata['match']['method'] ?? 'none'));
        $this->printSummary($summary);

        return $summary['enriched'] === 1 ? self::SUCCESS : self::FAILURE;
    }

    /**
     * @param  array{planned:int,searched:int,matched:int,enriched:int,skipped:int,failed:int}  $summary
     */
    private function printSummary(array $summary): void
    {
        foreach ($summary as $key => $value) {
            $this->line($key.': '.$value);
        }
    }
}
