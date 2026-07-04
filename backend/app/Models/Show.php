<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Show extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'external_source',
        'external_id',
        'title',
        'poster_url',
        'fanart_url',
        'followed',
        'seen_episodes',
        'aired_episodes',
        'runtime',
        'latest_seen_at',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function episodes(): HasMany
    {
        return $this->hasMany(Episode::class);
    }

    public function episodeWatches(): HasMany
    {
        return $this->hasMany(EpisodeWatch::class);
    }

    public function scopeForUser(Builder $query, User $user): Builder
    {
        return $query->where('user_id', $user->id);
    }

    public function scopeFollowed(Builder $query): Builder
    {
        return $query->where('followed', true);
    }

    protected function casts(): array
    {
        return [
            'followed' => 'boolean',
            'seen_episodes' => 'integer',
            'aired_episodes' => 'integer',
            'runtime' => 'integer',
            'latest_seen_at' => 'datetime',
        ];
    }
}
