<?php

namespace App\Console\Commands;

use App\Enums\MetadataReviewStatus;
use App\Models\Episode;
use App\Models\User;
use Illuminate\Console\Command;

class MetadataReviewQueueCommand extends Command
{
    protected $signature = 'mediahub:metadata-review-queue {user_id}';

    protected $description = 'Show safe grouped episode metadata failures for manual review.';

    public function handle(): int
    {
        $user = User::find($this->argument('user_id'));

        if (! $user) {
            $this->error('Metadata review queue failed: user was not found.');

            return self::FAILURE;
        }

        $groups = Episode::query()
            ->join('shows', 'shows.id', '=', 'episodes.show_id')
            ->where('episodes.user_id', $user->id)
            ->where('episodes.metadata_review_status', MetadataReviewStatus::Pending->value)
            ->whereNotNull('episodes.last_metadata_failure_reason')
            ->where('episodes.metadata_failure_count', '>', 0)
            ->groupBy('episodes.show_id', 'shows.title', 'episodes.season_number', 'episodes.last_metadata_failure_reason')
            ->orderBy('episodes.show_id')
            ->orderBy('episodes.season_number')
            ->get([
                'episodes.show_id',
                'shows.title as show_title',
                'episodes.season_number',
                'episodes.last_metadata_failure_reason',
            ])
            ->map(function (Episode $row) use ($user): object {
                $count = Episode::query()
                    ->where('user_id', $user->id)
                    ->where('show_id', $row->show_id)
                    ->where('season_number', $row->season_number)
                    ->where('last_metadata_failure_reason', $row->last_metadata_failure_reason)
                    ->where('metadata_review_status', MetadataReviewStatus::Pending->value)
                    ->count();

                return (object) [
                    'show_id' => $row->show_id,
                    'show_title' => $row->show_title,
                    'season_number' => $row->season_number,
                    'reason' => $row->last_metadata_failure_reason,
                    'count' => $count,
                ];
            });

        $this->line('review_groups: '.$groups->count());

        foreach ($groups as $group) {
            $this->line(sprintf(
                'show_id: %d | show: %s | season: %s | reason: %s | count: %d',
                $group->show_id,
                $group->show_title,
                $group->season_number ?? 'unknown',
                $group->reason,
                $group->count,
            ));
        }

        return self::SUCCESS;
    }
}
