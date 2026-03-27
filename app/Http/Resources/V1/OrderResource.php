<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $status = $this->status instanceof \BackedEnum ? $this->status->value : $this->status;

        $data = [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'status' => $status,
            'subtotal_cents' => $this->subtotal_cents,
            'delivery_fee_cents' => $this->delivery_fee_cents,
            'tax_cents' => $this->tax_cents,
            'total_cents' => $this->total_cents,
            'notes' => $this->notes,
            'delivery_location' => [
                'latitude' => $this->delivery_latitude,
                'longitude' => $this->delivery_longitude,
            ],
            'restaurant_id' => $this->restaurant_id,
            'driver_id' => $this->driver_id,
            'customer_id' => $this->customer_id,
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
            'restaurant' => new RestaurantResource($this->whenLoaded('restaurant')),
            'driver' => $this->when(
                $this->relationLoaded('driver') && $this->driver !== null,
                fn () => new DriverResource($this->driver)
            ),
            'customer' => $this->whenLoaded('customer', fn () => new UserResource($this->customer)),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];

        if (app(\App\Features\Services\FeatureFlagService::class)->isEnabled('show_eta')) {
            $data['eta'] = $this->resource->getAttribute('estimated_delivery_at');
        }

        return $data;
    }
}
