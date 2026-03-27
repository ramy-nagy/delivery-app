<?php

namespace App\Http\Controllers\Api\V1\Payment;

use App\Enums\PaymentMethod;
use App\Http\Controllers\Controller;
use App\Http\Requests\Payment\ProcessPaymentRequest;
use App\Http\Resources\V1\PaymentResource;
use App\Models\Order;
use App\Services\Payment\OrderPaymentService;
use Illuminate\Http\JsonResponse;
use RuntimeException;

class PaymentController extends Controller
{
    public function process(ProcessPaymentRequest $request, OrderPaymentService $payments): PaymentResource|JsonResponse
    {
        $order = Order::query()->findOrFail($request->validated('order_id'));
        $method = PaymentMethod::from($request->validated('method'));

        try {
            $payment = $payments->record($order, $request->user(), $method);
        } catch (RuntimeException $e) {
            return $this->error($e->getMessage(), null, 422);
        }

        return new PaymentResource($payment);
    }
}
