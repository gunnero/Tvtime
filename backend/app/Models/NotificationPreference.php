<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationPreference extends Model
{
    protected $fillable = [
        'user_id',
        'new_episodes',
        'movie_releases',
        'reminders',
        'in_app_enabled',
        'email_enabled',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeForUser(Builder $query, User $user): Builder
    {
        return $query->where('user_id', $user->id);
    }

    protected function casts(): array
    {
        return [
            'new_episodes' => 'boolean',
            'movie_releases' => 'boolean',
            'reminders' => 'boolean',
            'in_app_enabled' => 'boolean',
            'email_enabled' => 'boolean',
        ];
    }
}
