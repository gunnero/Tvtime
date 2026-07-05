<?php

namespace App\Services;

use App\Models\Episode;
use App\Models\EpisodeWatch;
use App\Models\MediaLink;
use App\Models\Movie;
use App\Models\MovieWatch;
use App\Models\Note;
use App\Models\Rating;
use App\Models\Show;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class MediaDetailService
{
    /**
     * @return array<string, mixed>
     */
    public function movie(User $user, Movie $movie): array
    {
        $movie = Movie::forUser($user)->findOrFail($movie->id);
        $watches = MovieWatch::forUser($user)
            ->where('movie_id', $movie->id)
            ->latest('watched_at')
            ->latest('id')
            ->limit(20)
            ->get();

        return [
            'id' => $movie->id,
            'movieId' => $movie->id,
            'kind' => 'movie',
            'title' => $movie->title,
            'subtitle' => 'Movie',
            'meta' => $movie->runtime > 0 ? $movie->runtime.' min movie' : 'Movie',
            'poster' => $movie->poster_url ?? '',
            'backdrop' => $movie->poster_url ?? '',
            'status' => $movie->is_to_watch ? 'watchlist' : ($watches->isNotEmpty() ? 'watched' : 'library'),
            'watched' => $watches->isNotEmpty(),
            'watchedCount' => $watches->count(),
            'rating' => $this->rating($user, 'movie', $movie->id),
            'notes' => $this->notes($user, 'movie', $movie->id),
            'watchHistory' => $watches->map(fn (MovieWatch $watch): array => $this->watchItem($watch))->values()->all(),
            'provider' => $this->providerStatus($user, 'movie', $movie->id),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function show(User $user, Show $show): array
    {
        $show = Show::forUser($user)->findOrFail($show->id);
        $watches = EpisodeWatch::forUser($user)
            ->where('show_id', $show->id)
            ->with('episode')
            ->latest('watched_at')
            ->latest('id')
            ->limit(20)
            ->get();

        return [
            'id' => $show->id,
            'showId' => $show->id,
            'kind' => 'show',
            'title' => $show->title,
            'subtitle' => $show->followed ? 'Followed show' : 'TV show',
            'meta' => $show->aired_episodes > 0
                ? $show->seen_episodes.'/'.$show->aired_episodes.' watched'
                : $watches->count().' watched episodes',
            'poster' => $show->poster_url ?? '',
            'backdrop' => $show->fanart_url ?? '',
            'status' => $show->followed ? 'followed' : ($watches->isNotEmpty() ? 'watched' : 'library'),
            'watched' => $watches->isNotEmpty() || $show->seen_episodes > 0,
            'watchedCount' => $watches->count(),
            'rating' => $this->rating($user, 'show', $show->id),
            'notes' => $this->notes($user, 'show', $show->id),
            'watchHistory' => $watches->map(fn (EpisodeWatch $watch): array => $this->episodeWatchItem($watch))->values()->all(),
            'provider' => $this->showProviderStatus($user, $show),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function episode(User $user, Episode $episode): array
    {
        $episode = Episode::forUser($user)->with('show')->findOrFail($episode->id);
        $watches = EpisodeWatch::forUser($user)
            ->where('episode_id', $episode->id)
            ->latest('watched_at')
            ->latest('id')
            ->limit(20)
            ->get();

        return [
            'id' => $episode->id,
            'episodeId' => $episode->id,
            'showId' => $episode->show_id,
            'showTitle' => $episode->show?->title,
            'kind' => 'episode',
            'title' => $episode->title ?: 'Untitled episode',
            'subtitle' => $this->episodeSubtitle($episode),
            'meta' => $episode->runtime > 0 ? $episode->runtime.' min episode' : 'Episode',
            'poster' => $episode->show?->poster_url ?? '',
            'backdrop' => $episode->show?->fanart_url ?? '',
            'status' => $watches->isNotEmpty() ? 'watched' : 'library',
            'watched' => $watches->isNotEmpty(),
            'watchedCount' => $watches->count(),
            'rating' => $this->rating($user, 'episode', $episode->id),
            'notes' => $this->notes($user, 'episode', $episode->id),
            'watchHistory' => $watches->map(fn (EpisodeWatch $watch): array => $this->episodeWatchItem($watch))->values()->all(),
            'provider' => $this->providerStatus($user, 'episode', $episode->id),
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function rating(User $user, string $mediaType, int $mediaId): ?array
    {
        $rating = Rating::forUser($user)->forMedia($mediaType, $mediaId)->first();

        if (! $rating) {
            return null;
        }

        return [
            'id' => $rating->id,
            'rating' => $rating->rating,
            'updatedAt' => $rating->updated_at?->toIso8601String(),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function notes(User $user, string $mediaType, int $mediaId): array
    {
        return Note::forUser($user)
            ->forMedia($mediaType, $mediaId)
            ->latest('updated_at')
            ->latest('id')
            ->limit(10)
            ->get()
            ->map(fn (Note $note): array => [
                'id' => $note->id,
                'body' => $note->body,
                'updatedAt' => $note->updated_at?->toIso8601String(),
            ])
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function watchItem(MovieWatch|EpisodeWatch $watch): array
    {
        return [
            'id' => $watch->id,
            'watchedAt' => $watch->watched_at?->toIso8601String(),
            'runtime' => $watch->runtime,
            'source' => $watch->source ?: 'archive',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function episodeWatchItem(EpisodeWatch $watch): array
    {
        return [
            ...$this->watchItem($watch),
            'episodeId' => $watch->episode_id,
            'title' => $watch->episode?->title,
            'subtitle' => $watch->episode ? $this->episodeSubtitle($watch->episode) : 'Episode',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function providerStatus(User $user, string $columnMediaType, int $mediaId): array
    {
        $column = $columnMediaType.'_id';
        $count = MediaLink::forUser($user)
            ->where($column, $mediaId)
            ->whereHas('sourceItem', fn (Builder $query) => $query
                ->forUser($user)
                ->whereHas('source', fn (Builder $sourceQuery) => $sourceQuery->forUser($user)))
            ->count();

        return [
            'linked' => $count > 0,
            'linkedItemsCount' => $count,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function showProviderStatus(User $user, Show $show): array
    {
        $episodeIds = Episode::forUser($user)->where('show_id', $show->id)->pluck('id');
        $count = MediaLink::forUser($user)
            ->where(function (Builder $query) use ($episodeIds, $show): void {
                $query->where('show_id', $show->id)
                    ->orWhereIn('episode_id', $episodeIds);
            })
            ->whereHas('sourceItem', fn (Builder $query) => $query
                ->forUser($user)
                ->whereHas('source', fn (Builder $sourceQuery) => $sourceQuery->forUser($user)))
            ->count();

        return [
            'linked' => $count > 0,
            'linkedItemsCount' => $count,
        ];
    }

    private function episodeSubtitle(Episode $episode): string
    {
        if ($episode->season_number && $episode->episode_number) {
            return 'S'.$episode->season_number.' E'.$episode->episode_number;
        }

        return 'Episode';
    }
}
