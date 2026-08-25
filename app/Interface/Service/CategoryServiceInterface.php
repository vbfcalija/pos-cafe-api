<?php

namespace App\Interface\Service;

interface CategoryServiceInterface
{
    public function findCategories(object $payload);

    public function findCategory(string $uuid);

    public function createCategory(object $payload);

    public function updateCategory(object $payload, string $uuid);

    public function deleteCategory(string $uuid);
}
