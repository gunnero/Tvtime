<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\MediaDiaryBackfillService;
use Illuminate\Console\Command;

class BackfillMediaDiaryCommand extends Command
{
    protected $signature = 'mediahub:backfill-diary {user_id} {--dry-run : Count missing historical events without writing}';

    protected $description = 'Backfill one user entertainment diary from canonical watches, ratings, notes, and import history.';

    public function handle(MediaDiaryBackfillService $backfill): int
    {
        $user = User::find($this->argument('user_id'));
        if (! $user) {
            $this->error('Diary backfill failed: user was not found.');

            return self::FAILURE;
        }

        foreach ($backfill->backfill($user, (bool) $this->option('dry-run')) as $key => $value) {
            $this->line($key.': '.$value);
        }

        return self::SUCCESS;
    }
}
