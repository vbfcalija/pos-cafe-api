<?php

namespace App\Interface\Repository;

use App\Models\Checklist;

interface ChecklistRepositoryInterface
{
    public function findMany(object $payload, string $sortField, string $sortOrder);

    public function findByUuid(string $uuid);

    public function generate(int $branchId, string $date): Checklist;

    public function update(Checklist $checklist, object $payload);

    public function updateTaskCompletion(Checklist $checklist, string $taskCompletionUuid, object $payload);

    public function addUser(Checklist $checklist, int $userId, string $type);

    public function removeUser(Checklist $checklist, string $checklistUserUuid);

    public function syncUsers(Checklist $checklist, string $type, array $userIds);
}
