<?php

namespace App\Enums;

enum MediaStatus: string
{
    case Planned = 'planned';
    case Watching = 'watching';
    case Watched = 'watched';
    case Archived = 'archived';
}
