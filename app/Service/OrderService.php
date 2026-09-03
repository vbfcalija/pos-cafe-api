<?php

namespace App\Service;

use App\Enums\DiscountType;
use App\Http\Resources\OrderResource;
use App\Interface\Repository\OrderRepositoryInterface;
use App\Interface\Service\OrderServiceInterface;
use App\Models\Discount;
use App\Models\Order;
use App\Models\ProductVariant;
use App\Models\Shift;
use App\Traits\SortingTraits;
use Blutrixx\EscPosPrinter\Facades\EscPosPrinter;
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

    /**
     * Prints on whichever Bluetooth ESC/POS thermal printer is already
     * bonded at the OS level — there's no in-app pairing flow, matching how
     * these printers are actually used at a till (paired once via Android
     * Bluetooth settings, then just left connected).
     */
    public function printReceipt(string $uuid)
    {
        $order = $this->orderRepository->findByUuid($uuid);
        $order->load('details.productVariant.product');

        $result = EscPosPrinter::print($this->formatReceipt($order));

        // The plugin returns different failure shapes depending on why it
        // failed: {error: '...'} for a real on-device failure (e.g. no
        // bonded printer), or {status: false, message: '...'} when the
        // native bridge itself isn't present at all (e.g. this endpoint hit
        // outside the packaged mobile app). Both mean nothing printed.
        if (isset($result['error']) || ($result['status'] ?? true) === false) {
            return response()->json([
                'message' => 'Printer not detected. Make sure a Bluetooth thermal printer is paired and try again.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return response()->json(['message' => 'Success.'], Response::HTTP_OK);
    }

    private function formatReceipt(Order $order): string
    {
        $branch = $order->shift?->branch;
        $lines = '';
        $subtotal = 0.0;
        $discountTotal = 0.0;
        $taxTotal = 0.0;

        foreach ($order->details as $detail) {
            $lineGross = $detail->quantity * (float) $detail->price;
            $discount = $detail->discount;

            // Prices are tax-inclusive: $afterDiscount is the actual amount
            // charged for this line. Tax is backed out of it for the
            // receipt's breakdown, never added on top.
            $afterDiscount = match ($discount?->type) {
                DiscountType::Percentage => round($lineGross - ($lineGross * (float) $discount->value / 100), 2),
                DiscountType::Amount => max(0.0, $lineGross - (float) $discount->value),
                default => $lineGross,
            };

            $rate = (float) $detail->tax_percentage / 100;
            $lineTax = round($afterDiscount * $rate / (1 + $rate), 2);

            $subtotal += $afterDiscount - $lineTax;
            $discountTotal += $lineGross - $afterDiscount;
            $taxTotal += $lineTax;

            $variantName = $detail->productVariant->name;
            $productName = $detail->productVariant->product->name;
            $label = $variantName === 'Regular' ? $productName : "{$productName} ({$variantName})";

            $lines .= "[L]{$label}\n";
            $lines .= "[L]{$detail->quantity} x ".number_format((float) $detail->price, 2).'[R]'.number_format($afterDiscount, 2)."\n";

            if ($discount) {
                $lines .= "[L]  Discount: {$discount->name}[R]-".number_format($lineGross - $afterDiscount, 2)."\n";
            }
        }

        $grandTotal = $subtotal + $taxTotal;
        $paymentMethods = $order->payments->pluck('payment_method.value')->unique()->implode(', ');

        return
            '[C]<b>'.config('app.name')."</b>\n".
            ($branch ? "[C]{$branch->name}\n" : '').
            "[C]================================\n".
            "[L]Order #: {$order->order_no}\n".
            "[L]Date: {$order->date->format('Y-m-d H:i')}\n".
            "[L]Cashier: {$order->user->firstname} {$order->user->lastname}\n".
            ($order->customer ? "[L]Customer: {$order->customer->name}\n" : '').
            "[C]--------------------------------\n".
            $lines.
            "[C]--------------------------------\n".
            '[L]Subtotal[R]'.number_format($subtotal, 2)."\n".
            '[L]Discount[R]-'.number_format($discountTotal, 2)."\n".
            '[L]Tax[R]'.number_format($taxTotal, 2)."\n".
            '[L]<b>TOTAL[R]'.number_format($grandTotal, 2)."</b>\n".
            "[C]--------------------------------\n".
            "[L]Payment: {$paymentMethods}\n".
            "[C]\n".
            "[C]<b>Thank you!</b>\n".
            "[C]Please come again.\n".
            "[C]\n";
    }
}
