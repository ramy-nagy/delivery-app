<?php

namespace App\Http\Controllers\Api\V1\Restaurant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Menu\StoreMenuItemRequest;
use App\Http\Requests\Menu\UpdateMenuItemRequest;
use App\Http\Resources\V1\MenuItemResource;
use App\Models\MenuItem;
use App\Models\Restaurant;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class MenuItemController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $restaurant = $this->ownedRestaurant($request);

        $items = MenuItem::query()
            ->where('restaurant_id', $restaurant->id)
            ->orderBy('sort_order')
            ->paginate(50);

        return MenuItemResource::collection($items);
    }

    public function store(StoreMenuItemRequest $request): MenuItemResource
    {
        $restaurant = $this->ownedRestaurant($request);

        $data = $request->validated();
        $priceCents = (int) round((float) $data['price'] * 100);

        $item = MenuItem::create([
            'restaurant_id' => $restaurant->id,
            'category' => $data['category'],
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'price_cents' => $priceCents,
            'is_available' => $data['is_available'] ?? true,
            'sort_order' => $data['sort_order'] ?? 0,
        ]);

        return new MenuItemResource($item);
    }

    public function update(UpdateMenuItemRequest $request, MenuItem $menuItem): MenuItemResource
    {
        $restaurant = $this->ownedRestaurant($request);

        if ((int) $menuItem->restaurant_id !== (int) $restaurant->id) {
            abort(403, 'This menu item belongs to another restaurant.');
        }

        $data = $request->validated();

        if (array_key_exists('price', $data)) {
            $menuItem->price_cents = (int) round((float) $data['price'] * 100);
            unset($data['price']);
        }

        $menuItem->fill($data);
        $menuItem->save();

        return new MenuItemResource($menuItem->fresh());
    }

    public function destroy(Request $request, MenuItem $menuItem): Response
    {
        $restaurant = $this->ownedRestaurant($request);

        if ((int) $menuItem->restaurant_id !== (int) $restaurant->id) {
            abort(403, 'This menu item belongs to another restaurant.');
        }

        $menuItem->delete();

        return response()->noContent();
    }

    private function ownedRestaurant(Request $request): Restaurant
    {
        $restaurant = $request->user()->ownedRestaurant;

        if ($restaurant === null) {
            abort(404, 'No restaurant linked to this account.');
        }

        return $restaurant;
    }
}
