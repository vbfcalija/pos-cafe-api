<?php

namespace App\Repository;

use App\Interface\Repository\OrderRepositoryInterface;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Shift;

class OrderRepository implements OrderRepositoryInterface
{
    public function create(object $payload)
    {
        $order = new Order;
        $order->shift_id = Shift::where('uuid', $payload->shift_uuid)->firstOrFail()->id;
        $order->customer_id = $payload->customer_uuid
            ? Customer::where('uuid', $payload->customer_uuid)->firstOrFail()->id
            : null;
        $order->order_no = $this->nextOrderNo();
        $order->date = now();
        $order->user_id = $payload->user()->id;
        $order->save();

        return $order->fresh();
    }

    public function addLine($order, array $line)
    {
        $order->details()->create($line);
    }

    public function addPayment($order, array $payment)
    {
        $order->payments()->create($payment);
    }

    public function nextOrderNo()
    {
        // Simple, single-cafe sequencing — no per-terminal range allocation needed here.
        return (string) (Order::max('id') + 1);
    }
}
