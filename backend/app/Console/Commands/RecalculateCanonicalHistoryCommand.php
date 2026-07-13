<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\CanonicalWatchHistoryService;
use Illuminate\Console\Command;

class RecalculateCanonicalHistoryCommand extends Command
{
    protected $signature = 'mediahub:recalculate-history {user_id}';

    protected $description = 'Recalculate canonical show progress from owned watch history';

    public function handle(CanonicalWatchHistoryService $history): int
    {
        $user = User::query()->find($this->argument('user_id'));
        if (! $user) {
            $this->error('User not found.');

            return self::FAILURE;
        }

        $summary = $history->recalculateUser($user);
        $this->table(['shows_updated', 'episodes_seen', 'episodes_aired'], [[$summary['shows_updated'], $summary['episodes_seen'], $summary['episodes_aired']]]);

        return self::SUCCESS;
    }
}
