<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MovieWatch extends Model
{
    protected $fillable = [
        'user_id',
        'movie_id',
        'watched_at',
        'runtime',
        'watch_count',
        'source',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function movie(): BelongsTo
    {
        return $this->belongsTo(Movie::class);
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
            'watch_count' => 'integer',
        ];
    }
}
