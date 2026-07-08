<?php

namespace App\Console\Commands;

use App\Enums\MetadataReviewStatus;
use App\Models\Episode;
use Illuminate\Console\Command;

class IgnoreEpisodeMetadataCommand extends Command
{
    protected $signature = 'mediahub:metadata-ignore-episode {episode_id}';

    protected $description = 'Ignore one episode for bulk metadata enrichment.';

    public function handle(): int
    {
        $episode = Episode::find($this->argument('episode_id'));

        if (! $episode) {
            $this->error('Metadata ignore failed: episode was not found.');

            return self::FAILURE;
        }

        $episode->forceFill([
            'metadata_review_status' => MetadataReviewStatus::Ignored->value,
            'metadata_failed_at' => $episode->metadata_failed_at ?: now(),
        ])->save();

        $this->line('episode_id: '.$episode->id);
        $this->line('metadata_review_status: '.$episode->metadata_review_status);

        return self::SUCCESS;
    }
}
