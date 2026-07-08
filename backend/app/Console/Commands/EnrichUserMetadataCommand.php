<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\MediaMetadataService;
use Illuminate\Console\Command;

class EnrichUserMetadataCommand extends Command
{
    protected $signature = 'mediahub:enrich-user
        {user_id}
        {--type=all : Metadata type to enrich: movies, shows, episodes, or all}
        {--limit= : Maximum number of records to process}
        {--only-missing : Only process records without refreshed TMDB metadata}
        {--only-parent-enriched : For episode enrichment, only process episodes whose parent show has TMDB metadata}
        {--dry-run : Count eligible records without calling TMDB or writing data}
        {--sleep-ms=0 : Milliseconds to pause between records}
        {--min-confidence=0 : Minimum title-match confidence from 0 to 1}';

    protected $description = 'Enrich one user library with optional TMDB metadata.';

    public function handle(MediaMetadataService $metadata): int
    {
        $user = User::find($this->argument('user_id'));

        if (! $user) {
            $this->error('Metadata enrichment failed: user was not found.');

            return self::FAILURE;
        }

        $options = $this->serviceOptions();

        if ($options === null) {
            return self::FAILURE;
        }

        $this->printSummary($metadata->enrichUser($user, $options));

        return self::SUCCESS;
    }

    /**
     * @return array{type:string,limit:int|null,only_missing:bool,only_parent_enriched:bool,dry_run:bool,sleep_ms:int,min_confidence:float}|null
     */
    private function serviceOptions(): ?array
    {
        $type = (string) $this->option('type');

        if (! in_array($type, ['movies', 'shows', 'episodes', 'all'], true)) {
            $this->error('Metadata enrichment failed: --type must be movies, shows, episodes, or all.');

            return null;
        }

        $limit = $this->option('limit');

        if ($limit !== null && (! is_numeric($limit) || (int) $limit < 1)) {
            $this->error('Metadata enrichment failed: --limit must be a positive integer.');

            return null;
        }

        $sleepMs = $this->option('sleep-ms');

        if (! is_numeric($sleepMs) || (int) $sleepMs < 0) {
            $this->error('Metadata enrichment failed: --sleep-ms must be zero or a positive integer.');

            return null;
        }

        $minConfidence = $this->option('min-confidence');

        if (! is_numeric($minConfidence) || (float) $minConfidence < 0 || (float) $minConfidence > 1) {
            $this->error('Metadata enrichment failed: --min-confidence must be between 0 and 1.');

            return null;
        }

        return [
            'type' => $type,
            'limit' => $limit === null ? null : (int) $limit,
            'only_missing' => (bool) $this->option('only-missing'),
            'only_parent_enriched' => (bool) $this->option('only-parent-enriched'),
            'dry_run' => (bool) $this->option('dry-run'),
            'sleep_ms' => (int) $sleepMs,
            'min_confidence' => (float) $minConfidence,
        ];
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
