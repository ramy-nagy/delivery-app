<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MenuCategorySingleResource extends JsonResource
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
            'sort_order' => $this->sort_order,
            'image' => $this->getFirstMediaUrl('image')  ?: 'https://logopond.com/logos/a447d60b6c1ffcfcb618ed05ecd9a679.png',
            'items' => MenuItemResource::collection($this->whenLoaded('items')),
        ];
    }
}
