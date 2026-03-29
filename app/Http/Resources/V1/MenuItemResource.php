<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MenuItemResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'restaurant_id' => $this->restaurant_id,
            'category_id' => $this->category_id,
            'category' => new \App\Http\Resources\V1\MenuCategoryResource($this->whenLoaded('category')),
            'name' => $this->name,
            'description' => $this->description,
            'price_cents' => $this->price_cents,
            'is_available' => (bool) $this->is_available,
            'sort_order' => $this->sort_order,
            'image_url' => $this->getFirstMediaUrl('image'),
        ];
    }
}
