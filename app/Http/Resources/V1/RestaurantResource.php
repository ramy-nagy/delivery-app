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
            'is_open' => $this->isOpen(),
                        'opening_hours' => $this->opening_hours,
            'minimum_order_cents' => $this->minimum_order_cents,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'logo_url' => $this->getFirstMediaUrl('logo') ?: 'https://logopond.com/logos/a447d60b6c1ffcfcb618ed05ecd9a679.png',
            'background_url' => $this->getFirstMediaUrl('background')  ?: 'https://logopond.com/logos/a447d60b6c1ffcfcb618ed05ecd9a679.png',
            'average_rating' => round($this->reviews()->avg('rating'), 2),
            'reviews_count' => $this->reviews()->count(),
            'reviews' => \App\Http\Resources\V1\ReviewResource::collection($this->whenLoaded('reviews')),
            'category' => $this->whenLoaded('category', fn () => [
                'id' => $this->category->id,
                'name' => $this->category->name,
                'slug' => $this->category->slug,
            ]),
            'menu_items' => MenuItemResource::collection($this->whenLoaded('menuItems')),
            'menu_category' => $this->whenLoaded('mainCategory', function () {
                return [
                    'id' => $this->mainCategory->id,
                    'name' => $this->mainCategory->name,
                    'slug' => $this->mainCategory->slug,
                    'image' => $this->mainCategory->image ? url('storage/' . $this->mainCategory->image) : null,
                ];
            }),
        ];
    }
}
