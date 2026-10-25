<?php

namespace App\Service;

use App\Http\Resources\TaskResource;
use App\Interface\Repository\TaskRepositoryInterface;
use App\Interface\Service\TaskServiceInterface;
use App\Traits\SortingTraits;

class TaskService implements TaskServiceInterface
{
    use SortingTraits;

    private $taskRepository;

    public function __construct(TaskRepositoryInterface $taskRepository)
    {
        $this->taskRepository = $taskRepository;
    }

    public function findTasks(object $payload)
    {
        $sortField = $this->sortField($payload, 'id');
        $sortOrder = $this->sortOrder($payload, 'asc');

        $tasks = $this->taskRepository->findMany($payload, $sortField, $sortOrder);

        return TaskResource::collection($tasks);
    }

    public function findTask(string $uuid)
    {
        $task = $this->taskRepository->findByUuid($uuid);

        return new TaskResource($task);
    }

    public function createTask(object $payload)
    {
        $task = $this->taskRepository->create($payload);

        return new TaskResource($task);
    }

    public function updateTask(object $payload, string $uuid)
    {
        $task = $this->taskRepository->update($payload, $uuid);

        return new TaskResource($task);
    }

    public function deleteTask(string $uuid)
    {
        $this->taskRepository->delete($uuid);

        return response()->json([
            'message' => 'Success.',
        ], 200);
    }
}
