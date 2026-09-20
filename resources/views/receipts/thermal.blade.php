<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        {{-- dompdf's float + overflow:hidden clearfix collapses these rows to
        zero height instead of clearing them, so left/right justified rows
        use a two-cell table instead — dompdf renders table layout reliably. --}}
        @page { margin: 0; }
        body { margin: 0; padding: 6px 8px; font-family: "DejaVu Sans Mono", monospace; font-size: 8px; line-height: 1.5; color: #000; }
        p { margin: 0; }
        .center { text-align: center; }
        .bold { font-weight: bold; }
        .row { width: 100%; border-collapse: collapse; }
        .row td { padding: 0; }
        .row .right { text-align: right; }
        hr { border: none; border-top: 1px dashed #000; margin: 4px 0; }
        .item { margin-bottom: 2px; }
    </style>
</head>
<body>
    <p class="center bold">{{ $appName }}</p>
    @if ($branchName)
        <p class="center">{{ $branchName }}</p>
    @endif
    <hr>
    <p>Order #: {{ $order->order_no }}</p>
    <p>Date: {{ $order->date->format('Y-m-d H:i') }}</p>
    <p>Cashier: {{ $order->user->firstname }} {{ $order->user->lastname }}</p>
    @if ($order->customer)
        <p>Customer: {{ $order->customer->name }}</p>
    @endif
    <hr>
    @foreach ($items as $item)
        <div class="item">
            <p>{{ $item['label'] }}</p>
            <table class="row"><tr>
                <td>{{ $item['quantity'] }} x {{ number_format($item['price'], 2) }}</td>
                <td class="right">{{ number_format($item['total'], 2) }}</td>
            </tr></table>
            @if ($item['discountName'])
                <table class="row"><tr>
                    <td>&nbsp;&nbsp;{{ $item['discountName'] }}</td>
                    <td class="right">-{{ number_format($item['discountAmount'], 2) }}</td>
                </tr></table>
            @endif
        </div>
    @endforeach
    <hr>
    <table class="row"><tr><td>Subtotal</td><td class="right">{{ number_format($subtotal, 2) }}</td></tr></table>
    <table class="row"><tr><td>Discount</td><td class="right">-{{ number_format($discountTotal, 2) }}</td></tr></table>
    <table class="row"><tr><td>Tax</td><td class="right">{{ number_format($taxTotal, 2) }}</td></tr></table>
    <table class="row bold"><tr><td>TOTAL</td><td class="right">{{ number_format($grandTotal, 2) }}</td></tr></table>
    <hr>
    <p>Payment: {{ $paymentMethods }}</p>
    <p>&nbsp;</p>
    <p class="center bold">Thank you!</p>
    <p class="center">Please come again.</p>
</body>
</html>
