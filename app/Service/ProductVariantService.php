<?php

namespace App\Service;

use App\Http\Resources\ProductVariantResource;
use App\Interface\Repository\ProductVariantRepositoryInterface;
use App\Interface\Service\ProductVariantServiceInterface;
use App\Traits\SortingTraits;

class ProductVariantService implements ProductVariantServiceInterface
{
    use SortingTraits;

    private $productVariantRepository;

    public function __construct(ProductVariantRepositoryInterface $productVariantRepository)
    {
        $this->productVariantRepository = $productVariantRepository;
    }

    public function findProductVariants(object $payload)
    {
        $sortField = $this->sortField($payload, 'name');
        $sortOrder = $this->sortOrder($payload, 'asc');

        $variants = $this->productVariantRepository->findMany($payload, $sortField, $sortOrder);

        return ProductVariantResource::collection($variants);
    }

    public function findProductVariant(string $uuid)
    {
        $variant = $this->productVariantRepository->findByUuid($uuid);

        return new ProductVariantResource($variant);
    }

    public function createProductVariant(object $payload)
    {
        $variant = $this->productVariantRepository->create($payload);

        return new ProductVariantResource($variant);
    }

    public function updateProductVariant(object $payload, string $uuid)
    {
        $variant = $this->productVariantRepository->update($payload, $uuid);

        return new ProductVariantResource($variant);
    }

    public function deleteProductVariant(string $uuid)
    {
        $this->productVariantRepository->delete($uuid);

        return response()->json([
            'message' => 'Success.',
        ], 200);
    }
}
