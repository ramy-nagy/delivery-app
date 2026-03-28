<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RestaurantResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'phone' => $this->phone,
            'is_open' => (bool) $this->is_open,
            'minimum_order_cents' => $this->minimum_order_cents,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'logo_url' => $this->getFirstMediaUrl('logo'),
            'background_url' => $this->getFirstMediaUrl('background'),
            'average_rating' => round($this->reviews()->avg('rating'), 2),
            'reviews_count' => $this->reviews()->count(),
            'reviews' => \App\Http\Resources\V1\ReviewResource::collection($this->whenLoaded('reviews')),
            'category' => $this->whenLoaded('category', fn () => [
                'id' => $this->category->id,
                'name' => $this->category->name,
                'slug' => $this->category->slug,
            ]),
            'menu_items' => MenuItemResource::collection($this->whenLoaded('menuItems')),
        ];
    }
}
