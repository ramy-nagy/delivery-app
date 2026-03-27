<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'restaurant_id' => $this->restaurant_id,
            'items' => $this->items ?? [],
            'restaurant' => new RestaurantResource($this->whenLoaded('restaurant')),
        ];
    }
}
