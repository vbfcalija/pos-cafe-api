<?php

namespace App\Interface\Service;

interface ProductVariantServiceInterface
{
    public function findProductVariants(object $payload);

    public function findProductVariant(string $uuid);

    public function createProductVariant(object $payload);

    public function updateProductVariant(object $payload, string $uuid);

    public function deleteProductVariant(string $uuid);
}
