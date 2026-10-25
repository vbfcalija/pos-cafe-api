<?php

namespace App\Interface\Service;

interface ChecklistServiceInterface
{
    public function findChecklists(object $payload);

    public function findChecklist(string $uuid);

    public function generateChecklist(object $payload);

    public function updateChecklist(object $payload, string $uuid);

    public function updateTaskCompletion(object $payload, string $uuid, string $taskCompletionUuid);

    public function addUser(object $payload, string $uuid);

    public function removeUser(string $uuid, string $checklistUserUuid);

    public function syncUsers(object $payload, string $uuid);
}
