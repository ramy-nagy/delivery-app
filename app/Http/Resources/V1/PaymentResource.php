<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $method = $this->method instanceof \BackedEnum ? $this->method->value : $this->method;
        $status = $this->status instanceof \BackedEnum ? $this->status->value : $this->status;

        return [
            'id' => $this->id,
            'order_id' => $this->order_id,
            'method' => $method,
            'status' => $status,
            'amount_cents' => $this->amount_cents,
            'currency' => $this->currency,
            'gateway_reference' => $this->gateway_reference,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
