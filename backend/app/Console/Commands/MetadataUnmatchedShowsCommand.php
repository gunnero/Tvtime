<?php

namespace App\Console\Commands;

use App\Models\Show;
use App\Models\User;
use Illuminate\Console\Command;

class MetadataUnmatchedShowsCommand extends Command
{
    protected $signature = 'mediahub:metadata-unmatched-shows {user_id}';

    protected $description = 'Show safe review rows for shows missing TMDB metadata.';

    public function handle(): int
    {
        $user = User::find($this->argument('user_id'));

        if (! $user) {
            $this->error('Unmatched show review failed: user was not found.');

            return self::FAILURE;
        }

        $shows = Show::forUser($user)
            ->whereNull('tmdb_id')
            ->withCount('episodeWatches')
            ->withMax('episodeWatches', 'watched_at')
            ->orderByDesc('episode_watches_count')
            ->orderBy('title')
            ->get();

        $this->line('unmatched_shows: '.$shows->count());

        $shows->each(function (Show $show): void {
            $latest = $show->episode_watches_max_watched_at
                ? (string) str($show->episode_watches_max_watched_at)->substr(0, 10)
                : 'none';

            $this->line(implode(' | ', [
                'show_id: '.$show->id,
                'title: '.$show->title,
                'watched_episodes: '.(int) $show->episode_watches_count,
                'aired_episodes: '.(int) $show->aired_episodes,
                'latest_watched_at: '.$latest,
                'match_status: unmatched',
            ]));
        });

        return self::SUCCESS;
    }
}
