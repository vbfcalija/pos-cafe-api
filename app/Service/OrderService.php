<?php

namespace App\Service;

use App\Http\Resources\OrderResource;
use App\Interface\Repository\OrderRepositoryInterface;
use App\Interface\Service\OrderServiceInterface;
use App\Models\Discount;
use App\Models\ProductVariant;
use App\Models\Shift;
use App\Traits\SortingTraits;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class OrderService implements OrderServiceInterface
{
    use SortingTraits;

    private $orderRepository;

    public function __construct(OrderRepositoryInterface $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }

    public function findOrders(object $payload)
    {
        $sortField = $this->sortField($payload, 'date');
        $sortOrder = $this->sortOrder($payload, 'desc');

        $orders = $this->orderRepository->findMany($payload, $sortField, $sortOrder);

        return OrderResource::collection($orders);
    }

    public function findOrder(string $uuid)
    {
        $order = $this->orderRepository->findByUuid($uuid);

        return new OrderResource($order);
    }

    /**
     * One transaction: the order, its lines, and its payments either all
     * exist or none do. Price, cost, and tax percentage are copied onto each
     * line from the variant/product/tax-rate as they stand right now — later
     * changes to those records must never alter this order's stored totals.
     */
    public function createOrder(object $payload)
    {
        $shift = Shift::where('uuid', $payload->shift_uuid)->first();

        if ($shift && ! $shift->is_open) {
            return response()->json([
                'message' => 'An open shift is required before creating an order.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if ($shift && $shift->user_id !== $payload->user()->id) {
            return response()->json([
                'message' => 'The selected shift does not belong to the current user.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $order = DB::transaction(function () use ($payload) {
            $order = $this->orderRepository->create($payload);

            foreach ($payload->lines as $line) {
                $variant = ProductVariant::with('product.taxRate')
                    ->where('uuid', $line['product_variant_uuid'])
                    ->firstOrFail();

                $discount = isset($line['discount_uuid'])
                    ? Discount::where('uuid', $line['discount_uuid'])->first()
                    : null;

                $this->orderRepository->addLine($order, [
                    'product_variant_id' => $variant->id,
                    'quantity' => $line['quantity'],
                    'price' => $variant->price,
                    'cost' => $variant->cost,
                    'tax_percentage' => $variant->product->taxRate->percentage,
                    'discount_id' => $discount?->id,
                ]);
            }

            foreach ($payload->payments as $payment) {
                $this->orderRepository->addPayment($order, [
                    'date' => now(),
                    'payment_method' => $payment['payment_method'],
                    'reference' => $payment['reference'] ?? null,
                    'user_id' => $payload->user()->id,
                ]);
            }

            return $order;
        });

        return new OrderResource(
            $order->load('shift.branch', 'customer', 'user', 'details.productVariant', 'details.discount', 'payments.user')
        );
    }
}
