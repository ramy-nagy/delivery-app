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
    public function index(Restaurant $restaurant)
    {
        $categories = \App\Models\MenuCategory::with(['items' => function ($query) use ($restaurant) {
            $query->where('restaurant_id', $restaurant->id)
                ->where('is_available', true)
                ->orderBy('sort_order');
        }])->orderBy('sort_order')->get();

        return \App\Http\Resources\V1\MenuCategoryResource::collection($categories);
    }
}
