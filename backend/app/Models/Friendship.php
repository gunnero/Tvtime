<?php

namespace App\Models;

use App\Enums\FriendshipStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Friendship extends Model
{
    protected $fillable = [
        'requester_user_id',
        'addressee_user_id',
        'blocked_by_user_id',
        'pair_key',
        'status',
        'accepted_at',
        'declined_at',
        'blocked_at',
    ];

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_user_id');
    }

    public function addressee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'addressee_user_id');
    }

    public function blockedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'blocked_by_user_id');
    }

    public function scopeForUser(Builder $query, User $user): Builder
    {
        return $query->where(fn (Builder $participant) => $participant
            ->where('requester_user_id', $user->id)
            ->orWhere('addressee_user_id', $user->id));
    }

    public function scopeAccepted(Builder $query): Builder
    {
        return $query->where('status', FriendshipStatus::Accepted);
    }

    public function otherUser(User $user): User
    {
        return $this->requester_user_id === $user->id ? $this->addressee : $this->requester;
    }

    protected function casts(): array
    {
        return [
            'status' => FriendshipStatus::class,
            'accepted_at' => 'datetime',
            'declined_at' => 'datetime',
            'blocked_at' => 'datetime',
        ];
    }
}
