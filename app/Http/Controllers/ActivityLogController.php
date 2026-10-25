<?php

namespace App\Http\Controllers;

use App\Interface\Service\ActivityLogServiceInterface;
use Illuminate\Http\Request;

/**
 * Read-only: the activity log is written by the app itself and can't be
 * edited or deleted through the API.
 */
class ActivityLogController extends Controller
{
    private $activityLogService;

    public function __construct(ActivityLogServiceInterface $activityLogService)
    {
        $this->activityLogService = $activityLogService;
    }

    public function index(Request $request)
    {
        return $this->activityLogService->findActivityLogs($request);
    }

    public function show(string $uuid)
    {
        return $this->activityLogService->findActivityLog($uuid);
    }
}
