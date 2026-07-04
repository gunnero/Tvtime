<?php

namespace App\Models;

use App\Enums\InviteStatus;
use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invite extends Model
{
    use HasFactory;

    protected $fillable = [
        'email',
        'token_hash',
        'role',
        'status',
        'invited_by_user_id',
        'accepted_by_user_id',
        'expires_at',
        'accepted_at',
    ];

    public function inviter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by_user_id');
    }

    public function acceptedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'accepted_by_user_id');
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', InviteStatus::Pending);
    }

    public function scopeExpired(Builder $query): Builder
    {
        return $query->where('status', InviteStatus::Expired);
    }

    public function scopeAccepted(Builder $query): Builder
    {
        return $query->where('status', InviteStatus::Accepted);
    }

    protected function casts(): array
    {
        return [
            'role' => UserRole::class,
            'status' => InviteStatus::class,
            'expires_at' => 'datetime',
            'accepted_at' => 'datetime',
        ];
    }
}
