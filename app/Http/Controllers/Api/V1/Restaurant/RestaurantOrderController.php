<?php

namespace App\Http\Controllers\Api\V1\Restaurant;

use App\Domain\Orders\Services\OrderService;
use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Order\RestaurantOrderStatusRequest;
use App\Http\Resources\V1\OrderResource;
use App\Models\Order;
use App\Models\Restaurant;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Request;

class RestaurantOrderController extends Controller
{
    public function __construct(
        private OrderService $orderService,
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $restaurant = $this->restaurantForUser($request);

        $orders = Order::query()
            ->where('restaurant_id', $restaurant->id)
            ->with(['items', 'customer', 'driver'])
            ->latest()
            ->paginate(30);

        return OrderResource::collection($orders);
    }

    public function show(Request $request, Order $order): OrderResource
    {
        $restaurant = $this->restaurantForUser($request);
        $this->assertOrderBelongs($order, $restaurant);

        return new OrderResource($order->load(['items', 'restaurant', 'driver', 'customer']));
    }

    public function updateStatus(RestaurantOrderStatusRequest $request, Order $order): OrderResource
    {
        $restaurant = $this->restaurantForUser($request);
        $this->assertOrderBelongs($order, $restaurant);

        $map = [
            'accepted' => OrderStatus::ACCEPTED,
            'preparing' => OrderStatus::PREPARING,
            'ready' => OrderStatus::READY,
            'cancelled' => OrderStatus::CANCELLED,
        ];

        $to = $map[$request->validated('status')];
        $updated = $this->orderService->transitionForRestaurant($order, $to);

        return new OrderResource($updated->load(['items', 'restaurant', 'driver', 'customer']));
    }

    private function restaurantForUser(Request $request): Restaurant
    {
        $restaurant = $request->user()->ownedRestaurant;

        if ($restaurant === null) {
            abort(404, 'No restaurant linked to this account.');
        }

        return $restaurant;
    }

    private function assertOrderBelongs(Order $order, Restaurant $restaurant): void
    {
        if ((int) $order->restaurant_id !== (int) $restaurant->id) {
            abort(403, 'This order does not belong to your restaurant.');
        }
    }
}
