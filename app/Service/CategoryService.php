<?php

namespace App\Service;

use App\Http\Resources\CategoryResource;
use App\Interface\Repository\CategoryRepositoryInterface;
use App\Interface\Service\CategoryServiceInterface;
use App\Traits\SortingTraits;

class CategoryService implements CategoryServiceInterface
{
    use SortingTraits;

    private $categoryRepository;

    public function __construct(CategoryRepositoryInterface $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    public function findCategories(object $payload)
    {
        $sortField = $this->sortField($payload, 'name');
        $sortOrder = $this->sortOrder($payload, 'asc');

        $categories = $this->categoryRepository->findMany($payload, $sortField, $sortOrder);

        return CategoryResource::collection($categories);
    }

    public function findCategory(string $uuid)
    {
        $category = $this->categoryRepository->findByUuid($uuid);

        return new CategoryResource($category);
    }

    public function createCategory(object $payload)
    {
        $category = $this->categoryRepository->create($payload);

        return new CategoryResource($category);
    }

    public function updateCategory(object $payload, string $uuid)
    {
        $category = $this->categoryRepository->update($payload, $uuid);

        return new CategoryResource($category);
    }

    public function deleteCategory(string $uuid)
    {
        $this->categoryRepository->delete($uuid);

        return response()->json([
            'message' => 'Success.',
        ], 200);
    }
}
