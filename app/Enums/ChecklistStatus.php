<?php

namespace App\Enums;

enum ChecklistStatus: string
{
    case Pending = 'pending';
    case Verified = 'verified';
}
