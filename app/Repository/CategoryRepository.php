<?php

namespace App\Repository;

use App\Interface\Repository\CategoryRepositoryInterface;
use App\Models\Category;

class CategoryRepository implements CategoryRepositoryInterface
{
    public function findMany(object $payload, string $sortField, string $sortOrder)
    {
        return Category::orderBy($sortField, $sortOrder)
            ->paginate(config('services.paginate'));
    }

    public function findByUuid(string $uuid)
    {
        return Category::where('uuid', $uuid)->firstOrFail();
    }

    public function create(object $payload)
    {
        $category = new Category;
        $category->name = $payload->name;
        $category->description = $payload->description ?? null;
        $category->color = $payload->color ?? null;
        $category->save();

        return $category->fresh();
    }

    public function update(object $payload, string $uuid)
    {
        $category = Category::where('uuid', $uuid)->firstOrFail();
        $category->name = $payload->name ?? $category->name;
        $category->description = $payload->description ?? $category->description;
        $category->color = $payload->color ?? $category->color;
        $category->save();

        return $category->fresh();
    }

    public function delete(string $uuid)
    {
        $category = Category::where('uuid', $uuid)->firstOrFail();
        $category->delete(); // rejected by the DB FK constraint if a product still uses it
    }
}
