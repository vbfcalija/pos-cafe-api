<?php

namespace App\Interface\Service;

interface BranchServiceInterface
{
    public function findBranches(object $payload);

    public function findBranch(string $uuid);

    public function createBranch(object $payload);

    public function updateBranch(object $payload, string $uuid);

    public function deleteBranch(string $uuid);
}
