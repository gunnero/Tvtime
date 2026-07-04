<?php

namespace App\Services;

use App\Enums\InviteStatus;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\Invite;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InviteService
{
    public function __construct(
        private readonly AnalyticsService $analytics,
        private readonly AuditLogService $auditLog,
    ) {}

    public function create(
        string $email,
        UserRole $role,
        ?User $inviter = null,
        ?\DateTimeInterface $expiresAt = null,
    ): string {
        $token = Str::random(64);
        $invite = Invite::create([
            'email' => strtolower($email),
            'token_hash' => $this->hashToken($token),
            'role' => $role,
            'status' => InviteStatus::Pending,
            'invited_by_user_id' => $inviter?->id,
            'expires_at' => $expiresAt ?? now()->addDays(7),
        ]);

        $this->analytics->record('invite.created', $inviter, [
            'invite_id' => $invite->id,
            'role' => $role->value,
        ]);
        $this->auditLog->record('invite.created', $inviter, $invite, null, [
            'invite_id' => $invite->id,
            'role' => $role->value,
        ]);

        return $token;
    }

    public function accept(string $token, string $name, string $password): User
    {
        return DB::transaction(function () use ($token, $name, $password): User {
            $invite = Invite::pending()
                ->where('token_hash', $this->hashToken($token))
                ->lockForUpdate()
                ->firstOrFail();

            if ($invite->expires_at->isPast()) {
                $invite->update(['status' => InviteStatus::Expired]);
                abort(422, 'Invite has expired.');
            }

            $user = User::create([
                'name' => $name,
                'email' => $invite->email,
                'password' => $password,
                'role' => $invite->role ?? UserRole::Member,
                'status' => UserStatus::Active,
            ]);

            $invite->update([
                'status' => InviteStatus::Accepted,
                'accepted_at' => now(),
                'accepted_by_user_id' => $user->id,
            ]);

            $this->analytics->record('invite.accepted', $user, [
                'invite_id' => $invite->id,
                'role' => $user->role->value,
            ]);
            $this->auditLog->record('invite.accepted', $user, $invite, $user, [
                'invite_id' => $invite->id,
                'role' => $user->role->value,
            ]);

            return $user;
        });
    }

    private function hashToken(string $token): string
    {
        return hash('sha256', $token);
    }
}
