<?php

namespace App\Http\Controllers\Api\V1\Restaurant;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\MenuItemResource;
use App\Models\Restaurant;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class MenuController extends Controller
{
    /**
     * Public menu listing for a restaurant (same payload as show restaurant, items only).
     */
    public function index(Restaurant $restaurant): AnonymousResourceCollection
    {
        $items = $restaurant->menuItems()
            ->where('is_available', true)
            ->orderBy('sort_order')
            ->get();

        return MenuItemResource::collection($items);
    }
}
