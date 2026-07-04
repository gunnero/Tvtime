<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EpisodeWatch extends Model
{
    protected $fillable = [
        'user_id',
        'show_id',
        'episode_id',
        'watched_at',
        'runtime',
        'source',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function show(): BelongsTo
    {
        return $this->belongsTo(Show::class);
    }

    public function episode(): BelongsTo
    {
        return $this->belongsTo(Episode::class);
    }

    public function scopeForUser(Builder $query, User $user): Builder
    {
        return $query->where('user_id', $user->id);
    }

    public function scopeWatched(Builder $query): Builder
    {
        return $query->whereNotNull('watched_at');
    }

    protected function casts(): array
    {
        return [
            'watched_at' => 'datetime',
            'runtime' => 'integer',
        ];
    }
}
