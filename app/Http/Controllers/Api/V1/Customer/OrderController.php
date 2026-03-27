<?php

namespace App\Http\Controllers\Api\V1\Customer;

use App\Application\Orders\Actions\CancelOrderAction;
use App\Application\Orders\Contracts\CreateOrderActionInterface;
use App\Application\Orders\Dto\CancelOrderDto;
use App\Application\Orders\Dto\CreateOrderDto;
use App\Models\Order;
use App\Domain\Shared\ValueObjects\Coordinate;
use App\Domain\Shared\ValueObjects\Money;
use App\Http\Controllers\Controller;
use App\Http\Requests\Order\CancelOrderRequest;
use App\Http\Requests\Order\CheckoutOrderRequest;
use App\Http\Requests\Order\PlaceOrderRequest;
use App\Http\Resources\V1\OrderResource;
use App\Models\Cart;
use App\Models\MenuItem;
use App\Models\Restaurant;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $orders = Order::query()
            ->where('customer_id', $request->user()->id)
            ->with(['items', 'restaurant', 'driver'])
            ->latest()
            ->paginate(20);

        return OrderResource::collection($orders);
    }

    public function show(Request $request, Order $order): OrderResource
    {
        $this->authorizeCustomerOrder($request, $order);

        return new OrderResource($order->load(['items', 'restaurant', 'driver']));
    }

    public function store(
        PlaceOrderRequest $request,
        CreateOrderActionInterface $action,
    ): OrderResource {
        $dto = CreateOrderDto::fromRequest($request);
        $order = $action->execute($dto);

        return new OrderResource($order->load(['items', 'restaurant', 'driver']));
    }

    public function checkout(
        CheckoutOrderRequest $request,
        CreateOrderActionInterface $action,
    ): OrderResource {
        $user = $request->user();
        $cart = Cart::query()->where('user_id', $user->id)->first();

        if ($cart === null || $cart->restaurant_id === null || $cart->items === []) {
            abort(422, 'Cart is empty.');
        }

        $restaurant = Restaurant::query()->findOrFail($cart->restaurant_id);
        if (! $restaurant->isOpen()) {
            abort(422, 'Restaurant is closed.');
        }

        $items = $cart->items;
        $restaurantId = (int) $cart->restaurant_id;

        $subtotalCents = 0;
        $normalized = [];

        foreach ($items as $line) {
            $menuItemId = (int) ($line['menu_item_id'] ?? 0);
            $qty = (int) ($line['quantity'] ?? 0);
            $menuItem = MenuItem::query()
                ->where('id', $menuItemId)
                ->where('restaurant_id', $restaurantId)
                ->where('is_available', true)
                ->first();

            if ($menuItem === null || $qty < 1) {
                abort(422, 'Cart contains invalid or unavailable items.');
            }

            $subtotalCents += $menuItem->price_cents * $qty;
            $normalized[] = [
                'menu_item_id' => $menuItemId,
                'quantity' => $qty,
                'options' => $line['options'] ?? [],
            ];
        }

        $deliveryFee = Money::fromFloat((float) $request->input('delivery_fee', 0));
        $tax = Money::fromFloat((float) $request->input('tax', 0));

        $dto = new CreateOrderDto(
            customerId: $user->id,
            restaurantId: $restaurantId,
            items: $normalized,
            deliveryLocation: Coordinate::fromArray($request->validated('delivery_location')),
            subtotal: Money::fromCents($subtotalCents),
            deliveryFee: $deliveryFee,
            tax: $tax,
            notes: $request->input('notes'),
        );

        $order = $action->execute($dto);

        $cart->update(['items' => [], 'restaurant_id' => null]);

        return new OrderResource($order->load(['items', 'restaurant', 'driver']));
    }

    public function cancel(
        CancelOrderRequest $request,
        Order $order,
        CancelOrderAction $cancelOrderAction,
    ): OrderResource {
        $this->authorizeCustomerOrder($request, $order);

        $dto = new CancelOrderDto($order->id, $request->input('reason'));
        $cancelled = $cancelOrderAction->handle($dto);

        return new OrderResource($cancelled->load(['items', 'restaurant', 'driver']));
    }

    private function authorizeCustomerOrder(Request $request, Order $order): void
    {
        if ((int) $order->customer_id !== (int) $request->user()->id) {
            abort(403, 'You cannot access this order.');
        }
    }
}
