<?php

namespace App\Enums;

/**
 * Which half of the daily checklist a task belongs to, and which half a
 * barista is assigned to (tasks.type and checklist_users.type).
 */
enum ChecklistType: string
{
    case Opener = 'opener';
    case Closer = 'closer';
}
