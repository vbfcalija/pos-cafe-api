<?php

namespace App\Service;

use App\Http\Resources\ActivityLogResource;
use App\Interface\Repository\ActivityLogRepositoryInterface;
use App\Interface\Service\ActivityLogServiceInterface;
use App\Traits\SortingTraits;

class ActivityLogService implements ActivityLogServiceInterface
{
    use SortingTraits;

    private $activityLogRepository;

    public function __construct(ActivityLogRepositoryInterface $activityLogRepository)
    {
        $this->activityLogRepository = $activityLogRepository;
    }

    public function findActivityLogs(object $payload)
    {
        $sortField = $this->sortField($payload, 'created_at');
        $sortOrder = $this->sortOrder($payload, 'desc');

        return ActivityLogResource::collection(
            $this->activityLogRepository->findMany($payload, $sortField, $sortOrder)
        );
    }

    public function findActivityLog(string $uuid)
    {
        return new ActivityLogResource($this->activityLogRepository->findByUuid($uuid));
    }
}
