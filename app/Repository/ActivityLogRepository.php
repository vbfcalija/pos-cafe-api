<?php

namespace App\Repository;

use App\Interface\Repository\ActivityLogRepositoryInterface;
use App\Models\ActivityLog;

class ActivityLogRepository implements ActivityLogRepositoryInterface
{
    public function findMany(object $payload, string $sortField, string $sortOrder)
    {
        $pageLength = min((int) ($payload->page_length ?? config('services.paginate')), 500);

        return ActivityLog::filter($payload->all())
            ->with('user')
            ->orderBy($sortField, $sortOrder)
            ->orderBy('id', 'desc')
            ->paginate($pageLength);
    }

    public function findByUuid(string $uuid)
    {
        return ActivityLog::with('user')->where('uuid', $uuid)->firstOrFail();
    }
}
