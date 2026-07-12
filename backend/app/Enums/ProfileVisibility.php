<?php

namespace App\Enums;

enum ProfileVisibility: string
{
    case Private = 'private';
    case Friends = 'friends';
    case Public = 'public';
}
