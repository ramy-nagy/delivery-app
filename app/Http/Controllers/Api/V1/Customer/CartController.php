<?php

namespace App\Http\Controllers\Api\V1\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Cart\SyncCartRequest;
use App\Http\Resources\V1\CartResource;
use App\Models\Cart;
use App\Models\MenuItem;
use Illuminate\Http\JsonResponse;
use App\Http\Traits\ApiResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    use ApiResponse;
    public function show(Request $request): JsonResponse
    {
        $cart = Cart::query()->firstOrCreate(
            ['user_id' => $request->user()->id],
            ['restaurant_id' => null, 'items' => []]
        );

        if ($cart->restaurant_id) {
            $cart->load('restaurant');
        }

        return $this->success(new CartResource($cart), 'Cart fetched successfully.');
    }

    public function sync(SyncCartRequest $request): JsonResponse
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

        return $this->success(new CartResource($cart), 'Cart synced successfully.');
    }

    public function destroy(Request $request, int $menuItemId): JsonResponse
    {
        $cart = Cart::query()->where('user_id', $request->user()->id)->first();

        if (!$cart) {
            abort(404, 'Cart not found.');
        }

        $items = $cart->items ?? [];
        $items = array_filter($items, function (array $item) use ($menuItemId) {
            return (int) $item['menu_item_id'] !== $menuItemId;
        });

        $cart->update(['items' => array_values($items)]);

        if ($cart->restaurant_id) {
            $cart->load('restaurant');
        }

        return $this->success(new CartResource($cart), 'Item removed from cart.');
    }
}
