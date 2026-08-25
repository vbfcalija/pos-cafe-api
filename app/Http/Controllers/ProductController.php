<?php

namespace App\Http\Controllers;

use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Interface\Service\ProductServiceInterface;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    private $productService;

    public function __construct(ProductServiceInterface $productService)
    {
        $this->productService = $productService;
    }

    public function index(Request $request)
    {
        return $this->productService->findProducts($request);
    }

    public function store(StoreProductRequest $request)
    {
        return $this->productService->createProduct($request);
    }

    public function show(string $uuid)
    {
        return $this->productService->findProduct($uuid);
    }

    public function update(UpdateProductRequest $request, string $uuid)
    {
        return $this->productService->updateProduct($request, $uuid);
    }

    public function destroy(string $uuid)
    {
        return $this->productService->deleteProduct($uuid);
    }
}
