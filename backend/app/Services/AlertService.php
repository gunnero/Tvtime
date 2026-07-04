<?php

namespace App\Services;

use App\Models\Alert;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class AlertService
{
    public function __construct(
        private readonly AnalyticsService $analytics,
    ) {}

    public function markRead(User $user, Alert $alert): Alert
    {
        if ($alert->user_id !== $user->id) {
            throw new ModelNotFoundException;
        }

        if ($alert->unread) {
            $alert->forceFill([
                'unread' => false,
                'read_at' => now(),
            ])->save();
        }

        $this->analytics->record('alert.read', $user, [
            'alert_id' => $alert->id,
            'category' => $alert->category,
        ]);

        return $alert->refresh();
    }

    public function markAllRead(User $user): int
    {
        $alerts = Alert::forUser($user)->unread()->get();

        foreach ($alerts as $alert) {
            $alert->forceFill([
                'unread' => false,
                'read_at' => now(),
            ])->save();
        }

        $this->analytics->record('alert.read_all', $user, [
            'updated' => $alerts->count(),
        ]);

        return $alerts->count();
    }
}
