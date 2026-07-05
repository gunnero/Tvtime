<?php

namespace App\Console\Commands;

use App\Models\Show;
use App\Services\MediaMetadataService;
use Illuminate\Console\Command;

class EnrichShowMetadataCommand extends Command
{
    protected $signature = 'mediahub:enrich-show {show_id}';

    protected $description = 'Enrich one show and its episodes with optional TMDB metadata.';

    public function handle(MediaMetadataService $metadata): int
    {
        $show = Show::find($this->argument('show_id'));

        if (! $show) {
            $this->error('Metadata enrichment failed: show was not found.');

            return self::FAILURE;
        }

        $this->printSummary($metadata->enrichShow($show, enrichEpisodes: true));

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
