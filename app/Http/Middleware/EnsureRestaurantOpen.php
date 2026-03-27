<?php

namespace App\Http\Middleware;

use App\Models\Restaurant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRestaurantOpen
{
    public function handle(Request $request, Closure $next): Response
    {
        $restaurantId = $request->route('restaurant')
            ?? $request->input('restaurant_id');

        if ($restaurantId instanceof Restaurant) {
            $restaurantId = $restaurantId->id;
        }

        if ($restaurantId === null) {
            return $next($request);
        }

        $restaurant = Restaurant::query()->find($restaurantId);

        if ($restaurant && ! $restaurant->isOpen()) {
            return response()->json(['message' => 'Restaurant is closed.'], 422);
        }

        return $next($request);
    }
}
