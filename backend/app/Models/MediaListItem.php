<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MediaListItem extends Model
{
    protected $fillable = ['user_id', 'media_list_id', 'media_type', 'media_id', 'position'];

    public function list(): BelongsTo
    {
        return $this->belongsTo(MediaList::class, 'media_list_id');
    }

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
        return ['media_id' => 'integer', 'position' => 'integer'];
    }
}
