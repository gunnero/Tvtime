<?php

namespace App\Services;

use App\Enums\FriendInviteStatus;
use App\Models\Alert;
use App\Models\FriendInvite;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\GoneHttpException;

class FriendInviteService
{
    public function __construct(
        private readonly FriendshipService $friendships,
        private readonly UserProfileService $profiles,
    ) {}

    /** @return array<string, mixed> */
    public function create(User $inviter): array
    {
        $token = Str::random(48);
        $invite = FriendInvite::create([
            'inviter_user_id' => $inviter->id,
            'token_hash' => $this->hashToken($token),
            'status' => FriendInviteStatus::Pending,
            'expires_at' => now()->addDays(7),
        ]);

        return [
            'id' => $invite->id,
            'status' => $invite->status->value,
            'url' => rtrim((string) config('app.url'), '/').'/invite/'.$token,
            'expiresAt' => $invite->expires_at->toIso8601String(),
        ];
    }

    /** @return array<string, mixed> */
    public function preview(string $token): array
    {
        $invite = $this->activeInvite($token);
        if ($invite->status === FriendInviteStatus::Pending) {
            $invite->forceFill([
                'status' => FriendInviteStatus::Opened,
                'opened_at' => now(),
            ])->save();
        }

        return [
            'status' => $invite->refresh()->status->value,
            'inviter' => $this->profiles->publicIdentity($invite->inviter),
            'expiresAt' => $invite->expires_at->toIso8601String(),
        ];
    }

    /** @return array<string, mixed> */
    public function accept(string $token, User $acceptingUser): array
    {
        return DB::transaction(function () use ($acceptingUser, $token): array {
            $invite = FriendInvite::query()
                ->where('token_hash', $this->hashToken($token))
                ->with('inviter')
                ->lockForUpdate()
                ->firstOrFail();
            $this->assertUsable($invite);

            if ($invite->inviter_user_id === $acceptingUser->id) {
                throw ValidationException::withMessages(['invite' => 'You cannot accept your own invitation.']);
            }

            $friendship = $this->friendships->acceptInvite($invite->inviter, $acceptingUser);
            $invite->forceFill([
                'status' => FriendInviteStatus::Accepted,
                'accepted_by_user_id' => $acceptingUser->id,
                'accepted_at' => now(),
            ])->save();
            $this->notifyAccepted($invite, $acceptingUser);

            return [
                'status' => $invite->status->value,
                'friendship' => $this->friendships->response($friendship, $acceptingUser),
            ];
        });
    }

    /** @return array<string, mixed> */
    public function listFor(User $user): array
    {
        $invites = FriendInvite::forUser($user)->latest()->limit(25)->get();

        return [
            'invites' => $invites->map(fn (FriendInvite $invite): array => [
                'id' => $invite->id,
                'status' => $invite->status->value,
                'expiresAt' => $invite->expires_at->toIso8601String(),
                'openedAt' => $invite->opened_at?->toIso8601String(),
                'acceptedAt' => $invite->accepted_at?->toIso8601String(),
            ])->values()->all(),
        ];
    }

    public function revoke(User $user, FriendInvite $invite): void
    {
        if ($invite->inviter_user_id !== $user->id) {
            throw new ModelNotFoundException;
        }
        if ($invite->status === FriendInviteStatus::Accepted) {
            throw ValidationException::withMessages(['invite' => 'An accepted invitation cannot be revoked.']);
        }

        $invite->forceFill([
            'status' => FriendInviteStatus::Revoked,
            'revoked_at' => now(),
        ])->save();
    }

    private function activeInvite(string $token): FriendInvite
    {
        $invite = FriendInvite::query()
            ->where('token_hash', $this->hashToken($token))
            ->with('inviter')
            ->firstOrFail();
        $this->assertUsable($invite);

        return $invite;
    }

    private function assertUsable(FriendInvite $invite): void
    {
        if ($invite->expires_at->isPast()) {
            $invite->forceFill(['status' => FriendInviteStatus::Expired])->save();
            throw new GoneHttpException('This invitation has expired.');
        }
        if (in_array($invite->status, [FriendInviteStatus::Expired, FriendInviteStatus::Revoked], true)) {
            throw new GoneHttpException('This invitation is no longer available.');
        }
        if ($invite->status === FriendInviteStatus::Accepted) {
            throw ValidationException::withMessages(['invite' => 'This invitation has already been accepted.']);
        }
    }

    private function notifyAccepted(FriendInvite $invite, User $acceptingUser): void
    {
        $identity = $this->profiles->publicIdentity($acceptingUser);
        Alert::updateOrCreate(
            ['user_id' => $invite->inviter_user_id, 'dedupe_key' => 'friend-invite-accepted:'.$invite->id],
            [
                'category' => 'social',
                'title' => 'Friend invitation accepted',
                'subtitle' => $identity['displayName'],
                'due_text' => 'Now',
                'payload' => ['kind' => 'friend_invite', 'profile_slug' => $identity['slug']],
                'unread' => true,
                'read_at' => null,
            ],
        );
    }

    private function hashToken(string $token): string
    {
        return hash('sha256', $token);
    }
}
