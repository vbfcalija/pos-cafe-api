<?php

namespace App\Interface\Service;

interface ProductServiceInterface
{
    public function findProducts(object $payload);

    public function findProduct(string $uuid);

    public function createProduct(object $payload);

    public function updateProduct(object $payload, string $uuid);

    public function deleteProduct(string $uuid);
}
