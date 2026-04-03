<?php

namespace App\Domain\Orders\Services;

use App\Application\Orders\Dto\CreateOrderDto;
use App\Domain\Orders\Models\Order;
use App\Domain\Orders\Validators\CreateOrderValidator;
use App\Domain\Shared\Exceptions\ValidationException as DomainValidationException;
use App\Domain\Shared\Traits\TransactionalBehaviour;
use App\Domain\Orders\Exceptions\InvalidOrderStatusTransitionException;
use App\Enums\OrderStatus;
use App\Models\Driver;
use App\Models\MenuItem;
use Illuminate\Support\Str;

class OrderService
{
    use TransactionalBehaviour;

    public function validateOrderCreation(CreateOrderDto $dto): void
    {
        $validator = new CreateOrderValidator($dto);
        if (! $validator->validate()) {
            throw new DomainValidationException($validator->getErrors());
        }
    }

    public function create(CreateOrderDto $dto): Order
    {
        return $this->inTransaction(function () use ($dto) {
            $total = $dto->total();

            $order = Order::create([
                'uuid' => (string) Str::uuid(),
                'customer_id' => $dto->customerId,
                'restaurant_id' => $dto->restaurantId,
                'driver_id' => null,
                'status' => OrderStatus::PENDING,
                'subtotal_cents' => $dto->subtotal->cents(),
                'delivery_fee_cents' => $dto->deliveryFee->cents(),
                'tax_cents' => $dto->tax->cents(),
                'total_cents' => $total->cents(),
                'notes' => $dto->notes,
                'delivery_latitude' => $dto->deliveryLocation?->latitude,
                'delivery_longitude' => $dto->deliveryLocation?->longitude,
            ]);

            foreach ($dto->items as $row) {
                $menuItemId = (int) $row['menu_item_id'];
                $quantity = (int) $row['quantity'];
                $menuItem = MenuItem::query()->findOrFail($menuItemId);
                $unit = $menuItem->price_cents;
                $lineTotal = $unit * $quantity;

                $order->items()->create([
                    'menu_item_id' => $menuItemId,
                    'name_snapshot' => $menuItem->name,
                    'unit_price_cents' => $unit,
                    'quantity' => $quantity,
                    'options_snapshot' => $row['options'] ?? [],
                    'line_total_cents' => $lineTotal,
                ]);
            }

            $order->statusHistory()->create([
                'status' => OrderStatus::PENDING->value,
                'meta' => ['source' => 'api'],
            ]);

            return $order->load('items');
        });
    }

    public function findById(int $orderId): ?Order
    {
        return Order::query()->find($orderId);
    }

    public function updateStatus(Order $order, OrderStatus $status): Order
    {
        $order->status = $status;
        $order->save();
        $order->statusHistory()->create([
            'status' => $status->value,
            'meta' => [],
        ]);

        return $order;
    }

    public function cancel(Order $order, ?string $reason = null): Order
    {
        $order->status = OrderStatus::CANCELLED;
        $order->save();
        $order->statusHistory()->create([
            'status' => OrderStatus::CANCELLED->value,
            'meta' => ['reason' => $reason],
        ]);

        return $order;
    }

    public function assignDriver(Order $order, Driver $driver): Order
    {
        $order->driver_id = $driver->id;
        $order->save();

        return $order;
    }

    /**
     * Restaurant staff advances kitchen / acceptance workflow.
     */
    public function transitionForRestaurant(Order $order, OrderStatus $to): Order
    {
        $allowed = match ($order->status) {
            OrderStatus::PENDING => [OrderStatus::ACCEPTED, OrderStatus::CANCELLED],
            OrderStatus::ACCEPTED => [OrderStatus::PREPARING, OrderStatus::CANCELLED],
            OrderStatus::PREPARING => [OrderStatus::READY, OrderStatus::CANCELLED],
            default => [],
        };

        if (! in_array($to, $allowed, true)) {
            throw new InvalidOrderStatusTransitionException(
                "Cannot move order from {$order->status->value} to {$to->value} as restaurant"
            );
        }

        return $this->updateStatus($order, $to);
    }

    /**
     * Assigned driver pickup / delivery workflow.
     */
    public function transitionForDriver(Order $order, OrderStatus $to, Driver $driver): Order
    {
        if ($order->driver_id !== $driver->id) {
            throw new InvalidOrderStatusTransitionException('Order is not assigned to this driver');
        }

        $allowed = match ($order->status) {
            OrderStatus::READY => [OrderStatus::PICKED_UP],
            OrderStatus::PICKED_UP => [OrderStatus::DELIVERED],
            default => [],
        };

        if (! in_array($to, $allowed, true)) {
            throw new InvalidOrderStatusTransitionException(
                "Cannot move order from {$order->status->value} to {$to->value} as driver"
            );
        }

        return $this->updateStatus($order, $to);
    }

    /**
     * First driver to claim a ready, unassigned order.
     */
    public function claimByDriver(Order $order, Driver $driver): Order
    {
        if ($order->status !== OrderStatus::READY) {
            throw new InvalidOrderStatusTransitionException('Only ready orders can be claimed');
        }

        if ($order->driver_id !== null) {
            throw new InvalidOrderStatusTransitionException('Order already has a driver');
        }

        return $this->assignDriver($order->fresh(), $driver);
    }

    /**
     * Customer may cancel while the kitchen has not finished prep.
     */
    public function assertCustomerCanCancel(Order $order): void
    {
        $allowed = [
            OrderStatus::PENDING,
            OrderStatus::ACCEPTED,
            OrderStatus::PREPARING,
        ];

        if (! in_array($order->status, $allowed, true)) {
            throw new InvalidOrderStatusTransitionException('This order can no longer be cancelled by the customer');
        }
    }
}
