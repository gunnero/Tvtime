<?php

namespace App\Services;

use App\Models\AnalyticsEvent;
use App\Models\User;
use App\Services\Concerns\SanitizesMetadata;

class AnalyticsService
{
    use SanitizesMetadata;

    /**
     * @param  array<string, mixed>  $metadata
     */
    public function record(string $eventName, ?User $actor = null, array $metadata = []): AnalyticsEvent
    {
        return AnalyticsEvent::create([
            'actor_user_id' => $actor?->id,
            'event_name' => $eventName,
            'metadata' => $this->sanitizeMetadata($metadata),
            'occurred_at' => now(),
        ]);
    }
}
