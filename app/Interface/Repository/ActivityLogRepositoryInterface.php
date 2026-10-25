<?php

namespace App\Interface\Repository;

interface ActivityLogRepositoryInterface
{
    public function findMany(object $payload, string $sortField, string $sortOrder);

    public function findByUuid(string $uuid);
}
