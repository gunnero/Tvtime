<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Movie extends Model
{
    protected $fillable = [
        'user_id',
        'external_source',
        'external_id',
        'tmdb_id',
        'imdb_id',
        'tvdb_id',
        'title',
        'original_title',
        'overview',
        'poster_url',
        'poster_path',
        'backdrop_path',
        'release_date',
        'genres',
        'runtime',
        'status',
        'vote_average',
        'metadata',
        'metadata_refreshed_at',
        'is_to_watch',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function watches(): HasMany
    {
        return $this->hasMany(MovieWatch::class);
    }

    public function scopeForUser(Builder $query, User $user): Builder
    {
        return $query->where('user_id', $user->id);
    }

    public function scopeToWatch(Builder $query): Builder
    {
        return $query->where('is_to_watch', true);
    }

    protected function casts(): array
    {
        return [
            'tmdb_id' => 'integer',
            'release_date' => 'date',
            'genres' => 'array',
            'runtime' => 'integer',
            'vote_average' => 'float',
            'metadata' => 'array',
            'metadata_refreshed_at' => 'datetime',
            'is_to_watch' => 'boolean',
        ];
    }
}
