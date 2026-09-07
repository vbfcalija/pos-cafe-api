<?php

namespace App\Http\Controllers;

use App\Http\Requests\Order\StoreOrderRequest;
use App\Interface\Service\OrderServiceInterface;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    private $orderService;

    public function __construct(OrderServiceInterface $orderService)
    {
        $this->orderService = $orderService;
    }

    public function index(Request $request)
    {
        return $this->orderService->findOrders($request);
    }

    public function store(StoreOrderRequest $request)
    {
        return $this->orderService->createOrder($request);
    }

    public function show(string $uuid)
    {
        return $this->orderService->findOrder($uuid);
    }

    public function print(string $uuid)
    {
        return $this->orderService->printReceipt($uuid);
    }

    public function refund(Request $request, string $uuid)
    {
        return $this->orderService->refundOrder($uuid, $request);
    }
}
