<?php

namespace App\Services\Payment;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class OrderPaymentService
{
    public function record(Order $order, User $payer, PaymentMethod $method): Payment
    {
        if ($order->customer_id !== $payer->id) {
            throw new RuntimeException('You cannot pay for this order.');
        }

        if ($order->payments()->where('status', PaymentStatus::PAID)->exists()) {
            throw new RuntimeException('Order already has a completed payment.');
        }

        return DB::transaction(function () use ($order, $method) {
            return Payment::create([
                'order_id' => $order->id,
                'method' => $method,
                'status' => PaymentStatus::PAID,
                'amount_cents' => $order->total_cents,
                'currency' => 'USD',
                'gateway_reference' => null,
                'meta' => ['channel' => 'api'],
            ]);
        });
    }
}
