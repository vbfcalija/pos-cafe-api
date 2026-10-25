<?php

namespace App\Http\Controllers;

use App\Http\Requests\Task\StoreTaskRequest;
use App\Http\Requests\Task\UpdateTaskRequest;
use App\Interface\Service\TaskServiceInterface;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    private $taskService;

    public function __construct(TaskServiceInterface $taskService)
    {
        $this->taskService = $taskService;
    }

    public function index(Request $request)
    {
        return $this->taskService->findTasks($request);
    }

    public function store(StoreTaskRequest $request)
    {
        return $this->taskService->createTask($request);
    }

    public function show(string $uuid)
    {
        return $this->taskService->findTask($uuid);
    }

    public function update(UpdateTaskRequest $request, string $uuid)
    {
        return $this->taskService->updateTask($request, $uuid);
    }

    public function destroy(string $uuid)
    {
        return $this->taskService->deleteTask($uuid);
    }
}
