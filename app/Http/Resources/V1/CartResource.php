<?php

namespace App\Http\Resources\V1;

use App\Models\MenuItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $items = $this->formatItems();
        $totalCents = array_reduce($items, function (int $carry, array $item) {
            return $carry + ($item['item_total_cents'] ?? 0);
        }, 0);

        return [
            'restaurant_id' => $this->restaurant_id,
            'items' => $items,
            'total_cents' => $totalCents,
            'restaurant' => new RestaurantResource($this->whenLoaded('restaurant')),
        ];
    }

    /**
     * Format items with menu item name, image, price and total
     *
     * @return array<int, array<string, mixed>>
     */
    private function formatItems(): array
    {
        $items = $this->items ?? [];
        
        if (empty($items)) {
            return [];
        }

        $menuItemIds = array_column($items, 'menu_item_id');
        $menuItems = MenuItem::query()
            ->whereIn('id', $menuItemIds)
            ->get()
            ->keyBy('id');

        return array_map(function (array $item) use ($menuItems) {
            $menuItem = $menuItems->get($item['menu_item_id']);
            $priceCents = $menuItem?->price_cents ?? 0;
            $quantity = $item['quantity'] ?? 1;
            $itemTotalCents = $priceCents * $quantity;
            
            return [
                'menu_item_id' => $item['menu_item_id'],
                'quantity' => $quantity,
                'options' => $item['options'] ?? [],
                'name' => $menuItem?->name,
                'price_cents' => $priceCents,
                'item_total_cents' => $itemTotalCents,
                'image' => $menuItem?->getFirstMediaUrl('image') ?: 'https://encrypted-tbn2.gstatic.com/images?q=tbn:ANd9GcR_Ch6uLWwY2o6JS0e6HsSwyN2oa6AaBBEwcU_vpanL0gN0zQyd'
            ];
        }, $items);
    }
}
