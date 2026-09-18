<?php

namespace App\Service;

use App\Enums\DiscountType;
use App\Http\Resources\OrderResource;
use App\Interface\Repository\OrderRepositoryInterface;
use App\Interface\Service\OrderServiceInterface;
use App\Models\Discount;
use App\Models\Order;
use App\Models\Payment;
use App\Models\ProductVariant;
use App\Models\Shift;
use App\Traits\SortingTraits;
use Barryvdh\DomPDF\Facade\Pdf;
use Blutrixx\EscPosPrinter\Facades\EscPosPrinter;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Native\Mobile\Facades\Share;

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
            $order->load('shift.branch', 'customer', 'user', 'details.productVariant.product', 'details.discount', 'payments.user')
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

        // Require an explicit `printed: true` — that's the only thing the
        // native Android side (blutrixx's Print handler) returns on a
        // genuine success. Everything else, including a plain {error: '...'}
        // on-device (no bonded printer), is treated as failure — and so is
        // an *empty* result, which is what the plugin's ensureConnected()
        // silently falls back to reporting as "connected" when there's no
        // native bridge at all (this dev/browser environment, or
        // NativePHP's "Jump" hybrid dev-relay with nothing actually
        // connected). Trusting anything short of `printed: true` used to
        // read as success here, which is exactly backwards: print failures
        // must never look like success.
        if (($result['printed'] ?? false) !== true) {
            return response()->json([
                'message' => $result['error'] ?? 'Printer not detected. Make sure a Bluetooth thermal printer is paired and try again.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return response()->json(['message' => 'Success.'], Response::HTTP_OK);
    }

    /**
     * Renders the receipt as a PDF for the "Save as PDF" action in the
     * print-preview modal. Inside the packaged mobile app there's no
     * browser to hand a file download to — NativePHP's WebView doesn't
     * support downloads at all — so the PDF is saved to the app's own
     * storage and handed to Android's native share sheet instead, letting
     * the user save it to Files/Drive or open it in another app. Outside
     * the app (plain web/dev), it's just a normal browser download.
     */
    public function downloadReceipt(string $uuid)
    {
        $order = $this->orderRepository->findByUuid($uuid);
        $order->load('details.productVariant.product');

        $filename = "receipt-{$order->order_no}.pdf";

        // Custom paper size in points (58mm ≈ 164pt wide, matching the
        // Bluetooth ESC/POS print width) — height is generously oversized
        // since dompdf paginates a fixed page rather than growing it to fit
        // content, and a receipt is always far shorter than this.
        $pdf = Pdf::loadView('receipts.thermal', $this->receiptViewData($order))
            ->setPaper([0, 0, 164, 2000]);

        if (env('NATIVEPHP_RUNNING')) {
            $path = storage_path("app/private/{$filename}");
            $pdf->save($path);
            Share::file("Receipt #{$order->order_no}", config('app.name').' receipt', $path);

            return response()->json(['message' => 'Success.', 'delivered_via' => 'share']);
        }

        return $pdf->download($filename);
    }

    /**
     * The app is now accessed as a plain website (no native app wrapper),
     * so NativePHP's Bluetooth bridge (printReceipt() above) is unreachable
     * — that only exists inside a compiled app. A browser also can't talk
     * to this printer directly: it's classic Bluetooth SPP, and the Web
     * Bluetooth API only supports BLE, so there is no in-browser path to
     * it at all. RawBT (a local Android app) bridges that gap — a web page
     * hands it raw ESC/POS bytes via its `rawbt:<base64>` URL scheme, and
     * RawBT does the actual SPP Bluetooth printing using real Android
     * APIs. This returns those bytes, base64-encoded, for the frontend to
     * hand off to RawBT; there's no way to get a real success/failure
     * signal back from that handoff, so the frontend must not claim the
     * print definitely succeeded.
     */
    public function receiptEscPos(string $uuid)
    {
        $order = $this->orderRepository->findByUuid($uuid);
        $order->load('details.productVariant.product');

        return response()->json([
            'data' => base64_encode($this->buildEscPosBytes($this->receiptViewData($order))),
        ]);
    }

    /**
     * A refund never deletes or rewrites the original sale. It records the
     * operator and timestamp so the transaction remains auditable.
     */
    public function refundOrder(string $uuid, object $payload)
    {
        $order = DB::transaction(function () use ($uuid, $payload) {
            $order = Order::where('uuid', $uuid)->lockForUpdate()->firstOrFail();

            if ($order->refunded_at) {
                return null;
            }

            $order->update([
                'refunded_at' => now(),
                'refunded_by_user_id' => $payload->user()->id,
            ]);

            return $order;
        });

        if (! $order) {
            return response()->json([
                'message' => 'This order has already been refunded.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return new OrderResource($this->orderRepository->findByUuid($uuid));
    }

    public function updatePayment(string $uuid, string $paymentUuid, object $payload)
    {
        $order = Order::where('uuid', $uuid)->firstOrFail();

        $payment = Payment::where('uuid', $paymentUuid)
            ->where('order_id', $order->id)
            ->firstOrFail();

        $payment->update([
            'payment_method' => $payload->payment_method,
            'reference' => $payload->reference ?: null,
            'user_id' => $payload->user()->id,
        ]);

        return new OrderResource($this->orderRepository->findByUuid($uuid));
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
            $lines .= "[L]{$detail->quantity} x " . number_format((float) $detail->price, 2) . '[R]' . number_format($afterDiscount, 2) . "\n";

            if ($discount) {
                $lines .= "[L]  Discount: {$discount->name}[R]-" . number_format($lineGross - $afterDiscount, 2) . "\n";
            }
        }

        $grandTotal = $subtotal + $taxTotal;
        $paymentMethods = $order->payments->pluck('payment_method.value')->unique()->implode(', ');

        return
            '[C]<b>' . config('app.name') . "</b>\n" .
            ($branch ? "[C]{$branch->name}\n" : '') .
            "[C]================================\n" .
            "[L]Order #: {$order->order_no}\n" .
            "[L]Date: {$order->date->format('Y-m-d H:i')}\n" .
            "[L]Cashier: {$order->user->firstname} {$order->user->lastname}\n" .
            ($order->customer ? "[L]Customer: {$order->customer->name}\n" : '') .
            "[C]--------------------------------\n" .
            $lines .
            "[C]--------------------------------\n" .
            '[L]Subtotal[R]' . number_format($subtotal, 2) . "\n" .
            '[L]Discount[R]-' . number_format($discountTotal, 2) . "\n" .
            '[L]Tax[R]' . number_format($taxTotal, 2) . "\n" .
            '[L]<b>TOTAL[R]' . number_format($grandTotal, 2) . "</b>\n" .
            "[C]--------------------------------\n" .
            "[L]Payment: {$paymentMethods}\n" .
            "[C]\n" .
            "[C]<b>Thank you!</b>\n" .
            "[C]Please come again.\n" .
            "[C]\n";
    }

    /**
     * Same tax-inclusive math as formatReceipt(), structured for the
     * receipts.thermal Blade view instead of ESC/POS [L]/[C]/[R] tags.
     * Duplicated rather than shared with formatReceipt() so the
     * already-verified Bluetooth print path can't be affected by changes
     * made here.
     */
    private function receiptViewData(Order $order): array
    {
        $items = [];
        $subtotal = 0.0;
        $discountTotal = 0.0;
        $taxTotal = 0.0;

        foreach ($order->details as $detail) {
            $lineGross = $detail->quantity * (float) $detail->price;
            $discount = $detail->discount;

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

            $items[] = [
                'label' => $variantName === 'Regular' ? $productName : "{$productName} ({$variantName})",
                'quantity' => $detail->quantity,
                'price' => (float) $detail->price,
                'total' => $afterDiscount,
                'discountName' => $discount?->name,
                'discountAmount' => $discount ? $lineGross - $afterDiscount : null,
            ];
        }

        return [
            'appName' => config('app.name'),
            'branchName' => $order->shift?->branch?->name,
            'order' => $order,
            'items' => $items,
            'subtotal' => $subtotal,
            'discountTotal' => $discountTotal,
            'taxTotal' => $taxTotal,
            'grandTotal' => $subtotal + $taxTotal,
            'paymentMethods' => $order->payments->pluck('payment_method.value')->unique()->implode(', '),
        ];
    }

    /**
     * Real ESC/POS bytes for RawBT to hand to the printer as-is — same
     * content/tax math as receiptViewData() feeds the PDF view, same
     * 32-column width and command set verified against real hardware
     * earlier (bold via ESC E, center/left align via ESC a, partial cut
     * via GS V). Kept separate from formatReceipt()'s [L]/[C]/[R] tags,
     * which are that *other* plugin's own markup, not real ESC/POS.
     */
    private function buildEscPosBytes(array $data): string
    {
        $esc = "\x1b";
        $gs = "\x1d";
        $width = 32;

        $boldOn = $esc.'E'."\x01";
        $boldOff = $esc.'E'."\x00";
        $alignCenter = $esc.'a'."\x01";
        $alignLeft = $esc.'a'."\x00";

        $lr = function (string $left, string $right) use ($width) {
            $space = max(1, $width - strlen($left) - strlen($right));

            return $left.str_repeat(' ', $space).$right;
        };

        $out = $esc.'@'; // initialize
        $out .= $alignCenter.$boldOn.$data['appName']."\n".$boldOff;
        if ($data['branchName']) {
            $out .= $data['branchName']."\n";
        }
        $out .= $alignLeft;
        $out .= str_repeat('-', $width)."\n";
        $out .= 'Order #: '.$data['order']->order_no."\n";
        $out .= 'Date: '.$data['order']->date->format('Y-m-d H:i')."\n";
        $out .= 'Cashier: '.$data['order']->user->firstname.' '.$data['order']->user->lastname."\n";
        if ($data['order']->customer) {
            $out .= 'Customer: '.$data['order']->customer->name."\n";
        }
        $out .= str_repeat('-', $width)."\n";

        foreach ($data['items'] as $item) {
            foreach (explode("\n", wordwrap($item['label'], $width, "\n", true)) as $labelLine) {
                $out .= $labelLine."\n";
            }
            $out .= $lr($item['quantity'].' x '.number_format($item['price'], 2), number_format($item['total'], 2))."\n";

            if ($item['discountName']) {
                $out .= $lr('  '.$item['discountName'], '-'.number_format($item['discountAmount'], 2))."\n";
            }
        }

        $out .= str_repeat('-', $width)."\n";
        $out .= $lr('Subtotal', number_format($data['subtotal'], 2))."\n";
        $out .= $lr('Discount', '-'.number_format($data['discountTotal'], 2))."\n";
        $out .= $lr('Tax', number_format($data['taxTotal'], 2))."\n";
        $out .= $boldOn.$lr('TOTAL', number_format($data['grandTotal'], 2))."\n".$boldOff;
        $out .= str_repeat('-', $width)."\n";
        $out .= 'Payment: '.$data['paymentMethods']."\n";
        $out .= "\n";
        $out .= $alignCenter;
        $out .= "Thank you!\n";
        $out .= "Please come again.\n";
        $out .= $alignLeft;
        $out .= "\n\n\n";
        $out .= $gs.'V'."\x01"; // partial cut

        return $out;
    }
}
