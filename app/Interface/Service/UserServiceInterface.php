<?php

namespace App\Interface\Service;

interface UserServiceInterface
{
    public function findUsers(object $payload);

    public function findUser(string $uuid);

    public function createUser(object $payload);

    public function updateUser(object $payload, string $uuid);

    public function deleteUser(string $uuid);
}
