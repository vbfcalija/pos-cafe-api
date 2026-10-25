<?php

namespace App\Interface\Service;

interface TaskServiceInterface
{
    public function findTasks(object $payload);

    public function findTask(string $uuid);

    public function createTask(object $payload);

    public function updateTask(object $payload, string $uuid);

    public function deleteTask(string $uuid);
}
