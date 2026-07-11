<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\ImportRelationshipRepairService;
use Illuminate\Console\Command;

class RepairImportRelationshipsCommand extends Command
{
    protected $signature = 'mediahub:repair-import-relationships {user_id} {--dry-run : Inspect without writing} {--apply : Back up and apply deterministic repairs}';

    protected $description = 'Inspect or repair deterministic TV Time episode/show relationships for one user.';

    public function handle(ImportRelationshipRepairService $repair): int
    {
        $user = User::find($this->argument('user_id'));
        if (! $user) {
            $this->error('Relationship repair failed: user was not found.');

            return self::FAILURE;
        }
        if ($this->option('dry-run') && $this->option('apply')) {
            $this->error('Choose either --dry-run or --apply.');

            return self::FAILURE;
        }

        $apply = (bool) $this->option('apply');
        if ($apply && $this->call('mediahub:backup-user', ['user_id' => $user->id]) !== self::SUCCESS) {
            $this->error('Relationship repair stopped because the required backup failed.');

            return self::FAILURE;
        }

        $summary = $apply ? $repair->apply($user) : $repair->inspect($user);
        $this->line('mode: '.($apply ? 'applied' : 'dry-run'));
        foreach ($summary as $key => $value) {
            $this->line(str_replace('_', ' ', $key).': '.$value);
        }

        return self::SUCCESS;
    }
}
