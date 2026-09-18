<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        {{-- dompdf doesn't support flexbox — left/right justified rows use
        floats instead, the layout dompdf actually renders reliably. --}}
        @page { margin: 0; }
        body { margin: 0; padding: 6px 8px; font-family: "DejaVu Sans Mono", monospace; font-size: 8px; line-height: 1.5; color: #000; }
        p { margin: 0; }
        .center { text-align: center; }
        .bold { font-weight: bold; }
        .row { overflow: hidden; }
        .row .left { float: left; }
        .row .right { float: right; }
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
            <div class="row">
                <span class="left">{{ $item['quantity'] }} x {{ number_format($item['price'], 2) }}</span>
                <span class="right">{{ number_format($item['total'], 2) }}</span>
            </div>
            @if ($item['discountName'])
                <div class="row">
                    <span class="left">&nbsp;&nbsp;{{ $item['discountName'] }}</span>
                    <span class="right">-{{ number_format($item['discountAmount'], 2) }}</span>
                </div>
            @endif
        </div>
    @endforeach
    <hr>
    <div class="row"><span class="left">Subtotal</span><span class="right">{{ number_format($subtotal, 2) }}</span></div>
    <div class="row"><span class="left">Discount</span><span class="right">-{{ number_format($discountTotal, 2) }}</span></div>
    <div class="row"><span class="left">Tax</span><span class="right">{{ number_format($taxTotal, 2) }}</span></div>
    <div class="row bold"><span class="left">TOTAL</span><span class="right">{{ number_format($grandTotal, 2) }}</span></div>
    <hr>
    <p>Payment: {{ $paymentMethods }}</p>
    <p>&nbsp;</p>
    <p class="center bold">Thank you!</p>
    <p class="center">Please come again.</p>
</body>
</html>
