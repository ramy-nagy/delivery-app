<?php

namespace App\Http\Controllers\Api\V1\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Cart\SyncCartRequest;
use App\Http\Resources\V1\CartResource;
use App\Models\Cart;
use App\Models\MenuItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function show(Request $request): CartResource|JsonResponse
    {
        $cart = Cart::query()->firstOrCreate(
            ['user_id' => $request->user()->id],
            ['restaurant_id' => null, 'items' => []]
        );

        if ($cart->restaurant_id) {
            $cart->load('restaurant');
        }

        return new CartResource($cart);
    }

    public function sync(SyncCartRequest $request): CartResource
    {
        $user = $request->user();
        $restaurantId = (int) $request->validated('restaurant_id');
        $lines = $request->validated('items');

        foreach ($lines as $line) {
            $menuItemId = (int) $line['menu_item_id'];
            $exists = MenuItem::query()
                ->where('id', $menuItemId)
                ->where('restaurant_id', $restaurantId)
                ->where('is_available', true)
                ->exists();

            if (! $exists) {
                abort(422, "Menu item {$menuItemId} is not part of this restaurant or is unavailable.");
            }
        }

        $cart = Cart::query()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'restaurant_id' => $restaurantId,
                'items' => $lines,
            ]
        );

        $cart->load('restaurant');

        return new CartResource($cart);
    }

    public function clear(Request $request): JsonResponse
    {
        Cart::query()->where('user_id', $request->user()->id)->update([
            'restaurant_id' => null,
            'items' => [],
        ]);

        return $this->success(null, 'Cart cleared.');
    }
}
