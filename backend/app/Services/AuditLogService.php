<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use App\Services\Concerns\SanitizesMetadata;
use Illuminate\Database\Eloquent\Model;

class AuditLogService
{
    use SanitizesMetadata;

    /**
     * @param  array<string, mixed>  $metadata
     */
    public function record(
        string $action,
        ?User $actor = null,
        ?Model $subject = null,
        ?User $targetUser = null,
        array $metadata = [],
    ): AuditLog {
        return AuditLog::create([
            'actor_user_id' => $actor?->id,
            'action' => $action,
            'subject_type' => $subject ? $subject::class : null,
            'subject_id' => $subject?->getKey(),
            'target_user_id' => $targetUser?->id,
            'metadata' => $this->sanitizeMetadata($metadata),
            'created_at' => now(),
        ]);
    }
}
