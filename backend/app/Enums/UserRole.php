<?php

namespace App\Enums;

enum UserRole: string
{
    case Owner = 'owner';
    case Admin = 'admin';
    case Member = 'member';

    public function canManageUsers(): bool
    {
        return in_array($this, [self::Owner, self::Admin], true);
    }
}
