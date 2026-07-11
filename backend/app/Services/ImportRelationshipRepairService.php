<?php

namespace App\Services;

use App\Models\Episode;
use App\Models\EpisodeWatch;
use App\Models\Show;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ImportRelationshipRepairService
{
    /** @return array<string, int> */
    public function inspect(User $user): array
    {
        $summary = [
            'episodes_scanned' => 0,
            'episode_links_repairable' => 0,
            'watch_show_links_repairable' => 0,
            'watch_show_mismatches_repairable' => 0,
            'show_counters_repairable' => 0,
            'ambiguous_episodes' => 0,
            'ambiguous_watches' => 0,
            'aggregate_only_show_gaps' => 0,
        ];

        Episode::forUser($user)->with(['watches' => fn ($query) => $query->forUser($user)])->chunkById(500, function ($episodes) use ($user, &$summary): void {
            foreach ($episodes as $episode) {
                $summary['episodes_scanned']++;
                if ($episode->show_id === null) {
                    $showIds = $episode->watches->pluck('show_id')->filter()->unique()->values();
                    if ($showIds->count() === 1 && Show::forUser($user)->whereKey($showIds->first())->exists()) {
                        $summary['episode_links_repairable']++;
                    } else {
                        $summary['ambiguous_episodes']++;
                    }
                }

                foreach ($episode->watches as $watch) {
                    if ($watch->show_id === null && $episode->show_id !== null) {
                        $summary['watch_show_links_repairable']++;
                    } elseif ($watch->show_id !== null && $episode->show_id !== null && $watch->show_id !== $episode->show_id) {
                        $summary['watch_show_mismatches_repairable']++;
                    }
                }
            }
        });

        $summary['ambiguous_watches'] = EpisodeWatch::forUser($user)->whereNull('episode_id')->count();

        Show::forUser($user)->withCount([
            'episodes' => fn ($query) => $query->forUser($user),
            'episodeWatches' => fn ($query) => $query->forUser($user),
        ])->chunkById(250, function ($shows) use (&$summary): void {
            foreach ($shows as $show) {
                $expectedSeen = max((int) $show->seen_episodes, (int) $show->episode_watches_count);
                $expectedAired = max((int) $show->aired_episodes, (int) $show->episodes_count);
                if ($expectedSeen !== $show->seen_episodes || $expectedAired !== $show->aired_episodes) {
                    $summary['show_counters_repairable']++;
                }
                if ($show->seen_episodes > 0 && $show->episodes_count === 0) {
                    $summary['aggregate_only_show_gaps']++;
                }
            }
        });

        return $summary;
    }

    /** @return array<string, int> */
    public function apply(User $user): array
    {
        $summary = $this->inspect($user);
        $summary += ['episode_links_repaired' => 0, 'watch_show_links_repaired' => 0, 'watch_show_mismatches_repaired' => 0, 'show_counters_repaired' => 0];

        DB::transaction(function () use ($user, &$summary): void {
            Episode::forUser($user)->whereNull('show_id')->with(['watches' => fn ($query) => $query->forUser($user)])->chunkById(500, function ($episodes) use ($user, &$summary): void {
                foreach ($episodes as $episode) {
                    $showIds = $episode->watches->pluck('show_id')->filter()->unique()->values();
                    if ($showIds->count() !== 1 || ! Show::forUser($user)->whereKey($showIds->first())->exists()) {
                        continue;
                    }
                    $episode->forceFill(['show_id' => (int) $showIds->first()])->save();
                    $summary['episode_links_repaired']++;
                }
            });

            EpisodeWatch::forUser($user)->whereNotNull('episode_id')->with('episode')->chunkById(500, function ($watches) use ($user, &$summary): void {
                foreach ($watches as $watch) {
                    $episode = $watch->episode;
                    if (! $episode || $episode->user_id !== $user->id || $episode->show_id === null) {
                        continue;
                    }
                    if ($watch->show_id === null) {
                        $watch->forceFill(['show_id' => $episode->show_id])->save();
                        $summary['watch_show_links_repaired']++;
                    } elseif ($watch->show_id !== $episode->show_id) {
                        $watch->forceFill(['show_id' => $episode->show_id])->save();
                        $summary['watch_show_mismatches_repaired']++;
                    }
                }
            });

            Show::forUser($user)->withCount([
                'episodes' => fn ($query) => $query->forUser($user),
                'episodeWatches' => fn ($query) => $query->forUser($user),
            ])->chunkById(250, function ($shows) use ($user, &$summary): void {
                foreach ($shows as $show) {
                    $values = [
                        'seen_episodes' => max((int) $show->seen_episodes, (int) $show->episode_watches_count),
                        'aired_episodes' => max((int) $show->aired_episodes, (int) $show->episodes_count),
                    ];
                    $latest = $show->episodeWatches()->forUser($user)->max('watched_at');
                    if ($latest && (! $show->latest_seen_at || $show->latest_seen_at->lt($latest))) {
                        $values['latest_seen_at'] = $latest;
                    }
                    if ($values['seen_episodes'] === $show->seen_episodes && $values['aired_episodes'] === $show->aired_episodes && ! array_key_exists('latest_seen_at', $values)) {
                        continue;
                    }
                    $show->forceFill($values)->save();
                    $summary['show_counters_repaired']++;
                }
            });
        });

        return $summary;
    }
}
