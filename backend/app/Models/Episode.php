<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Episode extends Model
{
    protected $fillable = [
        'user_id',
        'show_id',
        'external_source',
        'external_id',
        'tmdb_id',
        'imdb_id',
        'tvdb_id',
        'season_number',
        'episode_number',
        'title',
        'original_title',
        'overview',
        'poster_path',
        'backdrop_path',
        'genres',
        'runtime',
        'air_date',
        'status',
        'vote_average',
        'metadata',
        'metadata_refreshed_at',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function show(): BelongsTo
    {
        return $this->belongsTo(Show::class);
    }

    public function watches(): HasMany
    {
        return $this->hasMany(EpisodeWatch::class);
    }

    public function scopeForUser(Builder $query, User $user): Builder
    {
        return $query->where('user_id', $user->id);
    }

    protected function casts(): array
    {
        return [
            'tmdb_id' => 'integer',
            'season_number' => 'integer',
            'episode_number' => 'integer',
            'genres' => 'array',
            'runtime' => 'integer',
            'air_date' => 'date',
            'vote_average' => 'float',
            'metadata' => 'array',
            'metadata_refreshed_at' => 'datetime',
        ];
    }
}
