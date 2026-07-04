<?php

namespace App\Filament\Resources\Invites\Pages;

use App\Enums\UserRole;
use App\Filament\Resources\Invites\InviteResource;
use App\Models\Invite;
use App\Services\InviteService;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;
use Illuminate\Support\Carbon;

class ManageInvites extends ManageRecords
{
    protected static string $resource = InviteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->using(function (array $data, InviteService $invites): Invite {
                    $token = $invites->create(
                        email: $data['email'],
                        role: UserRole::from($data['role']),
                        inviter: auth()->user(),
                        expiresAt: Carbon::parse($data['expires_at']),
                    );

                    session()->flash('created_invite_token', $token);

                    return Invite::latest('id')->firstOrFail();
                }),
        ];
    }
}
