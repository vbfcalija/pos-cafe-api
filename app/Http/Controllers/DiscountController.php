<?php

namespace App\Http\Controllers;

use App\Http\Requests\Discount\StoreDiscountRequest;
use App\Http\Requests\Discount\UpdateDiscountRequest;
use App\Interface\Service\DiscountServiceInterface;
use Illuminate\Http\Request;

class DiscountController extends Controller
{
    private $discountService;

    public function __construct(DiscountServiceInterface $discountService)
    {
        $this->discountService = $discountService;
    }

    public function index(Request $request)
    {
        return $this->discountService->findDiscounts($request);
    }

    public function store(StoreDiscountRequest $request)
    {
        return $this->discountService->createDiscount($request);
    }

    public function show(string $uuid)
    {
        return $this->discountService->findDiscount($uuid);
    }

    public function update(UpdateDiscountRequest $request, string $uuid)
    {
        return $this->discountService->updateDiscount($request, $uuid);
    }

    public function destroy(string $uuid)
    {
        return $this->discountService->deleteDiscount($uuid);
    }
}
