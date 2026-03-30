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
            'image' => $this->getFirstMediaUrl('image')  ?: 'https://encrypted-tbn2.gstatic.com/images?q=tbn:ANd9GcR_Ch6uLWwY2o6JS0e6HsSwyN2oa6AaBBEwcU_vpanL0gN0zQyd',
            'items' => MenuItemResource::collection($this->whenLoaded('items')),
        ];
    }
}
