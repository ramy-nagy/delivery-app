<?php

namespace App\Http\Controllers\Api\V1\Driver;

use App\Domain\Orders\Services\OrderService;
use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Order\DriverOrderStatusRequest;
use App\Http\Resources\V1\OrderResource;
use App\Models\Order;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Request;
class DriverOrderController extends Controller
{
    public function __construct(
        private OrderService $orderService,
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $driver = $request->user()->driver;
        if ($driver === null) {
            abort(404, 'Driver profile not found.');
        }

        $mine = Order::query()
            ->where('driver_id', $driver->id)
            ->with(['items', 'restaurant', 'driver'])
            ->latest()
            ->paginate(20);

        return OrderResource::collection($mine);
    }

    public function available(Request $request): AnonymousResourceCollection
    {
        $driver = $request->user()->driver;
        if ($driver === null) {
            abort(404, 'Driver profile not found.');
        }

        $pool = Order::query()
            ->where('status', OrderStatus::READY)
            ->whereNull('driver_id')
            ->with(['items', 'restaurant'])
            ->latest()
            ->paginate(20);

        return OrderResource::collection($pool);
    }

    public function show(Request $request, Order $order): OrderResource
    {
        $driver = $request->user()->driver;
        if ($driver === null) {
            abort(404, 'Driver profile not found.');
        }

        if ((int) $order->driver_id === (int) $driver->id) {
            return new OrderResource($order->load(['items', 'restaurant', 'driver']));
        }

        if ($order->status === OrderStatus::READY && $order->driver_id === null) {
            return new OrderResource($order->load(['items', 'restaurant', 'driver']));
        }

        abort(403, 'You cannot view this order.');
    }

    public function claim(Request $request, Order $order): OrderResource
    {
        $driver = $request->user()->driver;
        if ($driver === null) {
            abort(404, 'Driver profile not found.');
        }

        $this->orderService->claimByDriver($order, $driver);

        return new OrderResource($order->fresh()->load(['items', 'restaurant', 'driver']));
    }

    public function updateStatus(DriverOrderStatusRequest $request, Order $order): OrderResource
    {
        $driver = $request->user()->driver;
        if ($driver === null) {
            abort(404, 'Driver profile not found.');
        }

        $to = OrderStatus::from($request->validated('status'));

        $updated = $this->orderService->transitionForDriver($order, $to, $driver);

        return new OrderResource($updated->load(['items', 'restaurant', 'driver']));
    }
}
