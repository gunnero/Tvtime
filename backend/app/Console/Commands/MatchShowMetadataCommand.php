<?php

namespace App\Console\Commands;

use App\Models\Show;
use App\Services\MediaMetadataService;
use Illuminate\Console\Command;

class MatchShowMetadataCommand extends Command
{
    protected $signature = 'mediahub:match-show
        {show_id}
        {--tmdb-id= : TMDB show ID selected during manual review}';

    protected $description = 'Manually match one show to a TMDB show ID.';

    public function handle(MediaMetadataService $metadata): int
    {
        $show = Show::find($this->argument('show_id'));

        if (! $show) {
            $this->error('Manual show match failed: show was not found.');

            return self::FAILURE;
        }

        $tmdbId = $this->option('tmdb-id');

        if (! is_numeric($tmdbId) || (int) $tmdbId < 1) {
            $this->error('Manual show match failed: --tmdb-id must be a positive integer.');

            return self::FAILURE;
        }

        $summary = $metadata->matchShow($show, (int) $tmdbId);
        $show->refresh();

        $this->line('show_id: '.$show->id);
        $this->line('tmdb_id: '.($show->tmdb_id ?: 'none'));
        $this->line('match_method: '.($show->metadata['match']['method'] ?? 'none'));

        foreach ($summary as $key => $value) {
            $this->line($key.': '.$value);
        }

        return $summary['enriched'] > 0 ? self::SUCCESS : self::FAILURE;
    }
}
