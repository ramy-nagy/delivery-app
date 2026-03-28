<?php

namespace App\Http\Controllers\Api\V1\Restaurant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Restaurant\UpdateRestaurantProfileRequest;
use App\Http\Resources\V1\RestaurantResource;
use App\Models\Restaurant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class RestaurantController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Restaurant::query()->with(['category', 'reviews']);

        // if ($request->boolean('open_only', true)) {
        //     $query->where('is_open', true);
        // }

        if ($search = $request->query('q')) {
            $query->where(function ($q) use ($search): void {
                $q->where('name', 'like', '%'.$search.'%')
                    ->orWhere('description', 'like', '%'.$search.'%');
            });
        }

        $restaurants = $query->orderBy('name')->paginate(20);

        return RestaurantResource::collection($restaurants);
    }

    public function show(Request $request, Restaurant $restaurant): RestaurantResource
    {
        $restaurant->load([
            'category',
            'menuItems' => fn ($q) => $q->where('is_available', true)->orderBy('sort_order'),
            'reviews',
        ]);

        return new RestaurantResource($restaurant);
    }

    public function me(Request $request): RestaurantResource|JsonResponse
    {
        $restaurant = $request->user()->ownedRestaurant;

        if ($restaurant === null) {
            return $this->notFound('No restaurant linked to this account.');
        }

        $restaurant->load('category');

        return new RestaurantResource($restaurant);
    }

    public function update(UpdateRestaurantProfileRequest $request): RestaurantResource|JsonResponse
    {
        $restaurant = $request->user()->ownedRestaurant;

        if ($restaurant === null) {
            return $this->notFound('No restaurant linked to this account.');
        }

        $data = $request->validated();

        if (array_key_exists('minimum_order', $data)) {
            $restaurant->minimum_order_cents = (int) round((float) $data['minimum_order'] * 100);
            unset($data['minimum_order']);
        }

        $restaurant->fill($data);
        $restaurant->save();

        return new RestaurantResource($restaurant->fresh()->load('category'));
    }
}
