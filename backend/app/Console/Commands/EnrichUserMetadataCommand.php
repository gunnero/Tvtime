<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\MediaMetadataService;
use Illuminate\Console\Command;

class EnrichUserMetadataCommand extends Command
{
    protected $signature = 'mediahub:enrich-user {user_id}';

    protected $description = 'Enrich one user library with optional TMDB metadata.';

    public function handle(MediaMetadataService $metadata): int
    {
        $user = User::find($this->argument('user_id'));

        if (! $user) {
            $this->error('Metadata enrichment failed: user was not found.');

            return self::FAILURE;
        }

        $this->printSummary($metadata->enrichUser($user));

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
