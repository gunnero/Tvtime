<?php

namespace App\Console\Commands;

use App\Models\Alert;
use App\Models\User;
use App\Services\AlertService;
use Illuminate\Console\Command;

class SyncCanonicalAlertsCommand extends Command
{
    protected $signature = 'mediahub:sync-alerts {user_id}';

    protected $description = 'Synchronize release and continuation alerts from canonical media data.';

    public function handle(AlertService $alerts): int
    {
        $user = User::query()->find($this->argument('user_id'));
        if (! $user) {
            $this->error('Alert sync failed: user was not found.');

            return self::FAILURE;
        }

        $created = $alerts->syncForUser($user);
        $this->line('created: '.$created);
        $this->line('total: '.Alert::forUser($user)->count());
        $this->line('unread: '.Alert::forUser($user)->unread()->count());

        return self::SUCCESS;
    }
}
