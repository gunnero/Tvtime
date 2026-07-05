<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\MediaMetadataService;
use Illuminate\Console\Command;

class MetadataStatusCommand extends Command
{
    protected $signature = 'mediahub:metadata-status {user_id}';

    protected $description = 'Show safe TMDB metadata coverage counts for one user.';

    public function handle(MediaMetadataService $metadata): int
    {
        $user = User::find($this->argument('user_id'));

        if (! $user) {
            $this->error('Metadata status failed: user was not found.');

            return self::FAILURE;
        }

        foreach ($metadata->statusForUser($user) as $key => $value) {
            $this->line($key.': '.$value);
        }

        return self::SUCCESS;
    }
}
