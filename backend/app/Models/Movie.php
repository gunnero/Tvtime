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
        'title',
        'poster_url',
        'runtime',
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
            'runtime' => 'integer',
            'is_to_watch' => 'boolean',
        ];
    }
}
