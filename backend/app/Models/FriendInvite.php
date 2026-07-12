<?php

namespace App\Models;

use App\Enums\FriendInviteStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FriendInvite extends Model
{
    protected $fillable = [
        'inviter_user_id',
        'accepted_by_user_id',
        'token_hash',
        'status',
        'expires_at',
        'opened_at',
        'accepted_at',
        'revoked_at',
    ];

    public function inviter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'inviter_user_id');
    }

    public function acceptedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'accepted_by_user_id');
    }

    public function scopeForUser(Builder $query, User $user): Builder
    {
        return $query->where('inviter_user_id', $user->id);
    }

    protected function casts(): array
    {
        return [
            'status' => FriendInviteStatus::class,
            'expires_at' => 'datetime',
            'opened_at' => 'datetime',
            'accepted_at' => 'datetime',
            'revoked_at' => 'datetime',
        ];
    }
}
