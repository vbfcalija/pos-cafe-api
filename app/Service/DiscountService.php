<?php

namespace App\Service;

use App\Http\Resources\DiscountResource;
use App\Interface\Repository\DiscountRepositoryInterface;
use App\Interface\Service\DiscountServiceInterface;
use App\Traits\SortingTraits;

class DiscountService implements DiscountServiceInterface
{
    use SortingTraits;

    private $discountRepository;

    public function __construct(DiscountRepositoryInterface $discountRepository)
    {
        $this->discountRepository = $discountRepository;
    }

    public function findDiscounts(object $payload)
    {
        $sortField = $this->sortField($payload, 'name');
        $sortOrder = $this->sortOrder($payload, 'asc');

        $discounts = $this->discountRepository->findMany($payload, $sortField, $sortOrder);

        return DiscountResource::collection($discounts);
    }

    public function findDiscount(string $uuid)
    {
        $discount = $this->discountRepository->findByUuid($uuid);

        return new DiscountResource($discount);
    }

    public function createDiscount(object $payload)
    {
        $discount = $this->discountRepository->create($payload);

        return new DiscountResource($discount);
    }

    public function updateDiscount(object $payload, string $uuid)
    {
        $discount = $this->discountRepository->update($payload, $uuid);

        return new DiscountResource($discount);
    }

    public function deleteDiscount(string $uuid)
    {
        $this->discountRepository->delete($uuid);

        return response()->json([
            'message' => 'Discount deleted successfully',
        ], 200);
    }
}
