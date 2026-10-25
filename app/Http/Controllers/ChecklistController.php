<?php

namespace App\Http\Controllers;

use App\Http\Requests\Checklist\StoreChecklistRequest;
use App\Http\Requests\Checklist\StoreChecklistUserRequest;
use App\Http\Requests\Checklist\SyncChecklistUsersRequest;
use App\Http\Requests\Checklist\UpdateChecklistRequest;
use App\Http\Requests\Checklist\UpdateTaskCompletionRequest;
use App\Interface\Service\ChecklistServiceInterface;
use Illuminate\Http\Request;

class ChecklistController extends Controller
{
    private $checklistService;

    public function __construct(ChecklistServiceInterface $checklistService)
    {
        $this->checklistService = $checklistService;
    }

    public function index(Request $request)
    {
        return $this->checklistService->findChecklists($request);
    }

    /**
     * Generates (or returns the existing) checklist for a branch and day.
     */
    public function store(StoreChecklistRequest $request)
    {
        return $this->checklistService->generateChecklist($request);
    }

    public function show(string $uuid)
    {
        return $this->checklistService->findChecklist($uuid);
    }

    public function update(UpdateChecklistRequest $request, string $uuid)
    {
        return $this->checklistService->updateChecklist($request, $uuid);
    }

    public function updateTask(UpdateTaskCompletionRequest $request, string $uuid, string $taskCompletionUuid)
    {
        return $this->checklistService->updateTaskCompletion($request, $uuid, $taskCompletionUuid);
    }

    public function addUser(StoreChecklistUserRequest $request, string $uuid)
    {
        return $this->checklistService->addUser($request, $uuid);
    }

    public function removeUser(string $uuid, string $checklistUserUuid)
    {
        return $this->checklistService->removeUser($uuid, $checklistUserUuid);
    }

    public function syncUsers(SyncChecklistUsersRequest $request, string $uuid)
    {
        return $this->checklistService->syncUsers($request, $uuid);
    }
}
