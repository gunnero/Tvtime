<?php

namespace App\Services;

use App\Models\EpisodeWatch;
use App\Models\MediaLink;
use App\Models\MovieWatch;
use App\Models\Note;
use App\Models\PlaybackProgress;
use App\Models\PlaybackSourceItem;
use App\Models\Rating;
use App\Models\Show;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class MediaLibraryService
{
    /**
     * @return array<string, int>
     */
    public function statsFor(User $user): array
    {
        $ownedEpisodeWatches = fn () => EpisodeWatch::forUser($user)
            ->whereNotNull('watched_at')
            ->whereHas('episode', fn (Builder $query) => $query->forUser($user))
            ->whereHas('show', fn (Builder $query) => $query->forUser($user));
        $ownedMovieWatches = fn () => MovieWatch::forUser($user)
            ->whereNotNull('watched_at')
            ->whereHas('movie', fn (Builder $query) => $query->forUser($user));
        $episodeMinutes = (int) $ownedEpisodeWatches()->sum('runtime');
        $movieMinutes = (int) $ownedMovieWatches()->get(['runtime', 'watch_count'])
            ->sum(fn (MovieWatch $watch): int => $watch->runtime * max(1, $watch->watch_count));
        $movieWatchEvents = (int) $ownedMovieWatches()->get(['watch_count'])
            ->sum(fn (MovieWatch $watch): int => max(1, $watch->watch_count));
        $manualMovieWatches = (int) $ownedMovieWatches()->where('source', 'manual')->get(['watch_count'])
            ->sum(fn (MovieWatch $watch): int => max(1, $watch->watch_count));
        $autoMovieWatches = (int) $ownedMovieWatches()->where('source', 'provider')->get(['watch_count'])
            ->sum(fn (MovieWatch $watch): int => max(1, $watch->watch_count));
        $manualWatchesCount = $manualMovieWatches
            + $ownedEpisodeWatches()->where('source', 'manual')->count();
        $autoTrackedWatchesCount = $autoMovieWatches
            + $ownedEpisodeWatches()->where('source', 'provider')->count();

        return [
            'episodesWatched' => $ownedEpisodeWatches()->count(),
            'moviesWatched' => $movieWatchEvents,
            'hoursWatched' => (int) round(($episodeMinutes + $movieMinutes) / 60),
            'showsFollowed' => Show::forUser($user)->followed()->count(),
            'manualWatchesCount' => $manualWatchesCount,
            'autoTrackedWatchesCount' => $autoTrackedWatchesCount,
            'linkedProviderItemsCount' => MediaLink::forUser($user)
                ->whereHas('sourceItem', fn (Builder $query) => $query
                    ->forUser($user)
                    ->whereHas('source', fn (Builder $sourceQuery) => $sourceQuery->forUser($user)))
                ->count(),
            'unlinkedProviderItemsCount' => PlaybackSourceItem::forUser($user)
                ->whereHas('source', fn (Builder $query) => $query->forUser($user)->active())
                ->whereDoesntHave('mediaLink', fn (Builder $query) => $query->forUser($user))
                ->count(),
            'unsyncedSourceOnlyProgressCount' => PlaybackProgress::forUser($user)
                ->whereNull('movie_id')
                ->whereNull('episode_id')
                ->count(),
            'ratingsCount' => Rating::forUser($user)->count(),
            'notesCount' => Note::forUser($user)->count(),
        ];
    }
}
