<?php

namespace App\Services;

use App\Models\Episode;
use App\Models\EpisodeWatch;
use App\Models\MovieWatch;
use App\Models\Show;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CanonicalWatchHistoryService
{
    /** @return array{shows_updated:int,episodes_seen:int,episodes_aired:int} */
    public function recalculateUser(User $user): array
    {
        $summary = ['shows_updated' => 0, 'episodes_seen' => 0, 'episodes_aired' => 0];

        Show::forUser($user)->orderBy('id')->each(function (Show $show) use ($user, &$summary): void {
            $counts = $this->recalculateShow($user, $show);
            $summary['shows_updated']++;
            $summary['episodes_seen'] += $counts['seen'];
            $summary['episodes_aired'] += $counts['aired'];
        });

        return $summary;
    }

    /** @return array{seen:int,aired:int} */
    public function recalculateShow(User $user, Show $show): array
    {
        if ($show->user_id !== $user->id) {
            throw new ModelNotFoundException;
        }

        $seen = EpisodeWatch::forUser($user)
            ->where('show_id', $show->id)
            ->whereNotNull('watched_at')
            ->whereHas('episode', fn (Builder $query) => $query
                ->forUser($user)
                ->where('show_id', $show->id))
            ->distinct('episode_id')
            ->count('episode_id');
        $aired = Episode::forUser($user)
            ->where('show_id', $show->id)
            ->where('season_number', '>', 0)
            ->where('episode_number', '>', 0)
            ->whereNotNull('air_date')
            ->whereDate('air_date', '<=', now()->toDateString())
            ->count();
        $latest = EpisodeWatch::forUser($user)
            ->where('show_id', $show->id)
            ->whereNotNull('watched_at')
            ->max('watched_at');

        $show->forceFill([
            'seen_episodes' => $seen,
            'aired_episodes' => $aired,
            'latest_seen_at' => $latest,
        ])->save();

        return compact('seen', 'aired');
    }

    /** @return array{movie_watches:int,episode_watches:int,total_watches:int} */
    public function watchCounts(User $user): array
    {
        $movieWatches = (int) MovieWatch::forUser($user)
            ->whereNotNull('watched_at')
            ->whereHas('movie', fn (Builder $query) => $query->forUser($user))
            ->get(['watch_count'])
            ->sum(fn (MovieWatch $watch): int => max(1, $watch->watch_count));
        $episodeWatches = EpisodeWatch::forUser($user)
            ->whereNotNull('watched_at')
            ->whereHas('episode', fn (Builder $query) => $query->forUser($user))
            ->whereHas('show', fn (Builder $query) => $query->forUser($user))
            ->count();

        return [
            'movie_watches' => $movieWatches,
            'episode_watches' => $episodeWatches,
            'total_watches' => $movieWatches + $episodeWatches,
        ];
    }
}
