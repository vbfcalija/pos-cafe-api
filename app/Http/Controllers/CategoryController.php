<?php

namespace App\Http\Controllers;

use App\Http\Requests\Category\StoreCategoryRequest;
use App\Http\Requests\Category\UpdateCategoryRequest;
use App\Interface\Service\CategoryServiceInterface;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    private $categoryService;

    public function __construct(CategoryServiceInterface $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    public function index(Request $request)
    {
        return $this->categoryService->findCategories($request);
    }

    public function store(StoreCategoryRequest $request)
    {
        return $this->categoryService->createCategory($request);
    }

    public function show(string $uuid)
    {
        return $this->categoryService->findCategory($uuid);
    }

    public function update(UpdateCategoryRequest $request, string $uuid)
    {
        return $this->categoryService->updateCategory($request, $uuid);
    }

    public function destroy(string $uuid)
    {
        return $this->categoryService->deleteCategory($uuid);
    }
}
