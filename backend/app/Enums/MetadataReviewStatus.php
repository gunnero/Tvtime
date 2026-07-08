<?php

namespace App\Enums;

enum MetadataReviewStatus: string
{
    case Pending = 'pending';
    case Ignored = 'ignored';
    case ManuallyMatched = 'manually_matched';
}
