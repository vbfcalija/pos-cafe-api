<?php

namespace App\Http\Controllers;

use App\Http\Requests\Order\StoreOrderRequest;
use App\Http\Requests\Order\UpdatePaymentRequest;
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

    public function downloadReceipt(string $uuid)
    {
        return $this->orderService->downloadReceipt($uuid);
    }

    public function receiptEscPos(string $uuid)
    {
        return $this->orderService->receiptEscPos($uuid);
    }

    public function refund(Request $request, string $uuid)
    {
        return $this->orderService->refundOrder($uuid, $request);
    }

    public function updatePayment(UpdatePaymentRequest $request, string $uuid, string $paymentUuid)
    {
        return $this->orderService->updatePayment($uuid, $paymentUuid, $request);
    }
}
