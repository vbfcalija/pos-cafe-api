<?php

namespace App\Service;

use App\Http\Resources\ProductResource;
use App\Interface\Repository\ProductRepositoryInterface;
use App\Interface\Service\ProductServiceInterface;
use App\Traits\SortingTraits;

class ProductService implements ProductServiceInterface
{
    use SortingTraits;

    private $productRepository;

    public function __construct(ProductRepositoryInterface $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    public function findProducts(object $payload)
    {
        $sortField = $this->sortField($payload, 'name');
        $sortOrder = $this->sortOrder($payload, 'asc');

        $products = $this->productRepository->findMany($payload, $sortField, $sortOrder);

        return ProductResource::collection($products);
    }

    public function findProduct(string $uuid)
    {
        $product = $this->productRepository->findByUuid($uuid);

        return new ProductResource($product);
    }

    public function createProduct(object $payload)
    {
        $product = $this->productRepository->create($payload);

        return new ProductResource($product);
    }

    public function updateProduct(object $payload, string $uuid)
    {
        $product = $this->productRepository->update($payload, $uuid);

        return new ProductResource($product);
    }

    public function deleteProduct(string $uuid)
    {
        $this->productRepository->delete($uuid);

        return response()->json([
            'message' => 'Product deleted successfully',
        ], 200);
    }
}
