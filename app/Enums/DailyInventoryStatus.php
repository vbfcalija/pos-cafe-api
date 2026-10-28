<?php

namespace App\Enums;

enum DailyInventoryStatus: string
{
    case Pending = 'pending';
    case Verified = 'verified';
}
