<?php

namespace App\Service;

use App\Http\Resources\OrderResource;
use App\Interface\Repository\OrderRepositoryInterface;
use App\Interface\Service\OrderServiceInterface;
use App\Models\Discount;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;

class OrderService implements OrderServiceInterface
{
    private $orderRepository;

    public function __construct(OrderRepositoryInterface $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }

    /**
     * One transaction: the order, its lines, and its payments either all
     * exist or none do. Price, cost, and tax percentage are copied onto each
     * line from the variant/product/tax-rate as they stand right now — later
     * changes to those records must never alter this order's stored totals.
     */
    public function createOrder(object $payload)
    {
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
                    'qty' => $line['qty'],
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
            $order->load('branch', 'shift', 'customer', 'user', 'details.productVariant', 'details.discount', 'payments.user')
        );
    }
}
