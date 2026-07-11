<?php

namespace App\Services;

use App\Models\Episode;
use App\Models\Movie;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;

class CalendarService
{
    public function __construct(private readonly MediaMetadataService $metadata) {}

    /** @param array<string, mixed> $filters @return array<string, mixed> */
    public function forUser(User $user, array $filters = []): array
    {
        $timezone = $user->timezone ?? config('app.timezone');
        $start = filled($filters['date_from'] ?? null)
            ? CarbonImmutable::parse((string) $filters['date_from'], $timezone)->startOfDay()
            : CarbonImmutable::now($timezone)->startOfMonth();
        $end = filled($filters['date_to'] ?? null)
            ? CarbonImmutable::parse((string) $filters['date_to'], $timezone)->endOfDay()
            : $start->endOfMonth();
        $end = $end->min($start->addMonths(3)->endOfDay());
        $type = in_array($filters['type'] ?? 'all', ['all', 'movies', 'episodes'], true)
            ? (string) ($filters['type'] ?? 'all')
            : 'all';

        $items = collect();

        if ($type !== 'movies') {
            $items = $items->concat(
                Episode::forUser($user)
                    ->with(['show' => fn ($query) => $query->forUser($user)])
                    ->whereBetween('air_date', [$start->toDateString(), $end->toDateString()])
                    ->whereHas('show', fn (Builder $query) => $query
                        ->forUser($user)
                        ->where(fn (Builder $showQuery) => $showQuery->where('followed', true)->orWhere('seen_episodes', '>', 0)))
                    ->orderBy('air_date')
                    ->orderBy('show_id')
                    ->orderBy('season_number')
                    ->orderBy('episode_number')
                    ->get()
                    ->map(fn (Episode $episode): array => [
                        'id' => 'episode-'.$episode->id,
                        'kind' => 'episode',
                        'episodeId' => $episode->id,
                        'showId' => $episode->show_id,
                        'date' => $episode->air_date?->toDateString(),
                        'title' => $episode->show?->title ?? 'Untitled show',
                        'subtitle' => $this->episodeCode($episode).' · '.($episode->title ?: 'Episode'),
                        'poster' => $this->metadata->imageUrl($episode->show?->poster_path) ?: ($episode->show?->poster_url ?? ''),
                        'released' => $episode->air_date?->isPast() || $episode->air_date?->isToday(),
                    ])
            );
        }

        if ($type !== 'episodes') {
            $items = $items->concat(
                Movie::forUser($user)
                    ->whereBetween('release_date', [$start->toDateString(), $end->toDateString()])
                    ->where(fn (Builder $query) => $query->where('is_to_watch', true)->orWhereHas('watches'))
                    ->orderBy('release_date')
                    ->orderBy('title')
                    ->get()
                    ->map(fn (Movie $movie): array => [
                        'id' => 'movie-'.$movie->id,
                        'kind' => 'movie',
                        'movieId' => $movie->id,
                        'date' => $movie->release_date?->toDateString(),
                        'title' => $movie->title,
                        'subtitle' => 'Movie release',
                        'poster' => $this->metadata->imageUrl($movie->poster_path) ?: ($movie->poster_url ?? ''),
                        'released' => $movie->release_date?->isPast() || $movie->release_date?->isToday(),
                    ])
            );
        }

        $sorted = $items->filter(fn (array $item): bool => filled($item['date']))
            ->sortBy(fn (array $item): string => $item['date'].'|'.$item['title'])
            ->values();

        return [
            'range' => ['from' => $start->toDateString(), 'to' => $end->toDateString(), 'timezone' => $timezone],
            'type' => $type,
            'items' => $sorted->all(),
            'days' => $sorted->groupBy('date')->map->values()->all(),
        ];
    }

    private function episodeCode(Episode $episode): string
    {
        $season = max(0, (int) $episode->season_number);
        $number = max(0, (int) $episode->episode_number);

        return sprintf('S%02dE%02d', $season, $number);
    }
}
