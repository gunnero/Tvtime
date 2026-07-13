<?php

namespace App\Services;

use App\Enums\MetadataReviewStatus;
use App\Models\Episode;
use App\Models\Show;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class EpisodeCatalogService
{
    public function __construct(
        private readonly TMDBClientService $tmdb,
        private readonly MediaMetadataService $metadata,
        private readonly CanonicalWatchHistoryService $history,
    ) {}

    /**
     * @param  array<string, mixed>  $options
     * @return array{shows_planned:int,shows_synced:int,seasons_synced:int,episodes_created:int,episodes_updated:int,skipped:int,failed:int}
     */
    public function syncUser(User $user, array $options = []): array
    {
        $summary = $this->summary();
        $limit = max(0, (int) ($options['limit_shows'] ?? 0));
        $sleepMs = max(0, (int) ($options['sleep_ms'] ?? 0));
        $dryRun = (bool) ($options['dry_run'] ?? false);

        $query = Show::forUser($user)->whereNotNull('tmdb_id')->orderBy('id');
        if ($limit > 0) {
            $query->limit($limit);
        }

        $shows = $query->get();
        $summary['shows_planned'] = $shows->count();

        if (! $this->tmdb->enabled()) {
            $summary['skipped'] = $shows->count();

            return $summary;
        }

        foreach ($shows as $show) {
            $details = $this->tmdb->getShow((int) $show->tmdb_id);
            if (! $details) {
                $summary['failed']++;

                continue;
            }

            $seasons = collect($details['seasons'] ?? [])
                ->filter(fn (mixed $season): bool => is_array($season)
                    && (int) ($season['season_number'] ?? 0) > 0
                    && (int) ($season['episode_count'] ?? 0) > 0)
                ->sortBy('season_number')
                ->values();
            $showSucceeded = false;

            foreach ($seasons as $season) {
                $payload = $this->tmdb->getSeason((int) $show->tmdb_id, (int) $season['season_number']);
                if (! is_array($payload)) {
                    $summary['failed']++;

                    continue;
                }

                $summary['seasons_synced']++;
                $showSucceeded = true;
                foreach ($payload['episodes'] ?? [] as $details) {
                    if (! is_array($details)) {
                        $summary['skipped']++;

                        continue;
                    }

                    $seasonNumber = (int) ($details['season_number'] ?? $season['season_number']);
                    $episodeNumber = (int) ($details['episode_number'] ?? 0);
                    if ($seasonNumber <= 0 || $episodeNumber <= 0 || ! is_numeric($details['id'] ?? null)) {
                        $summary['skipped']++;

                        continue;
                    }

                    $existing = Episode::forUser($user)
                        ->where('show_id', $show->id)
                        ->where('season_number', $seasonNumber)
                        ->where('episode_number', $episodeNumber)
                        ->first();
                    $existing ??= Episode::forUser($user)->where('tmdb_id', (int) $details['id'])->first();

                    if ($existing && in_array($existing->metadata_review_status, [
                        MetadataReviewStatus::Ignored->value,
                        MetadataReviewStatus::ManuallyMatched->value,
                    ], true)) {
                        $summary['skipped']++;

                        continue;
                    }

                    if ($dryRun) {
                        $summary[$existing ? 'episodes_updated' : 'episodes_created']++;

                        continue;
                    }

                    DB::transaction(function () use ($details, $episodeNumber, $existing, $seasonNumber, $show, $user, &$summary): void {
                        $episode = $existing ?: Episode::create([
                            'user_id' => $user->id,
                            'show_id' => $show->id,
                            'external_source' => 'tmdb',
                            'external_id' => (string) $details['id'],
                            'season_number' => $seasonNumber,
                            'episode_number' => $episodeNumber,
                            'title' => (string) ($details['name'] ?? ''),
                        ]);
                        $this->metadata->applyCatalogEpisode($episode, $details);
                        $summary[$existing ? 'episodes_updated' : 'episodes_created']++;
                    });
                }

                if ($sleepMs > 0) {
                    usleep($sleepMs * 1000);
                }
            }

            if ($showSucceeded) {
                $summary['shows_synced']++;
                if (! $dryRun) {
                    $this->history->recalculateShow($user, $show->refresh());
                }
            }
        }

        return $summary;
    }

    /** @return array{shows_planned:int,shows_synced:int,seasons_synced:int,episodes_created:int,episodes_updated:int,skipped:int,failed:int} */
    private function summary(): array
    {
        return [
            'shows_planned' => 0,
            'shows_synced' => 0,
            'seasons_synced' => 0,
            'episodes_created' => 0,
            'episodes_updated' => 0,
            'skipped' => 0,
            'failed' => 0,
        ];
    }
}
