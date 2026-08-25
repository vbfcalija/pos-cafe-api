<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductVariant\StoreProductVariantRequest;
use App\Http\Requests\ProductVariant\UpdateProductVariantRequest;
use App\Interface\Service\ProductVariantServiceInterface;
use Illuminate\Http\Request;

class ProductVariantController extends Controller
{
    private $productVariantService;

    public function __construct(ProductVariantServiceInterface $productVariantService)
    {
        $this->productVariantService = $productVariantService;
    }

    public function index(Request $request)
    {
        return $this->productVariantService->findProductVariants($request);
    }

    public function store(StoreProductVariantRequest $request)
    {
        return $this->productVariantService->createProductVariant($request);
    }

    public function show(string $uuid)
    {
        return $this->productVariantService->findProductVariant($uuid);
    }

    public function update(UpdateProductVariantRequest $request, string $uuid)
    {
        return $this->productVariantService->updateProductVariant($request, $uuid);
    }

    public function destroy(string $uuid)
    {
        return $this->productVariantService->deleteProductVariant($uuid);
    }
}
