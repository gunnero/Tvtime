<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\EpisodeCatalogService;
use Illuminate\Console\Command;

class SyncEpisodeCatalogCommand extends Command
{
    protected $signature = 'mediahub:sync-episode-catalog
        {user_id}
        {--limit-shows= : Maximum number of matched shows to process}
        {--sleep-ms=50 : Milliseconds to pause between season requests}
        {--dry-run : Fetch and count catalog changes without writing}';

    protected $description = 'Synchronize complete TMDB season and episode catalogs for one user.';

    public function handle(EpisodeCatalogService $catalog): int
    {
        $user = User::find($this->argument('user_id'));
        if (! $user) {
            $this->error('Episode catalog sync failed: user was not found.');

            return self::FAILURE;
        }

        $limit = $this->option('limit-shows');
        $sleepMs = $this->option('sleep-ms');
        if (($limit !== null && (! is_numeric($limit) || (int) $limit < 1)) || ! is_numeric($sleepMs) || (int) $sleepMs < 0) {
            $this->error('Episode catalog sync failed: command options are invalid.');

            return self::FAILURE;
        }

        $summary = $catalog->syncUser($user, [
            'limit_shows' => $limit === null ? 0 : (int) $limit,
            'sleep_ms' => (int) $sleepMs,
            'dry_run' => (bool) $this->option('dry-run'),
        ]);
        foreach ($summary as $key => $value) {
            $this->line($key.': '.$value);
        }

        return $summary['failed'] > 0 ? self::FAILURE : self::SUCCESS;
    }
}
