<?php

namespace App\Interface\Repository;

interface UserRepositoryInterface
{
    public function findMany(object $payload, string $sortField, string $sortOrder);

    public function findByUuid(string $uuid);

    public function findByEmail(string $email);

    public function create(object $payload);

    public function update(object $payload, string $uuid);

    public function delete(string $uuid);
}
