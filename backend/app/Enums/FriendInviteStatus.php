<?php

namespace App\Enums;

enum FriendInviteStatus: string
{
    case Pending = 'pending';
    case Opened = 'opened';
    case Accepted = 'accepted';
    case Expired = 'expired';
    case Revoked = 'revoked';
}
