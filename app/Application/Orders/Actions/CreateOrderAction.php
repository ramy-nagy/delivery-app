<?php

namespace App\Application\Orders\Actions;

use App\Application\Orders\Contracts\CreateOrderActionInterface;
use App\Application\Orders\Dto\CreateOrderDto;
use App\Domain\Orders\Models\Order;
use App\Domain\Orders\Services\OrderService;
use App\Domain\Shared\Services\TransactionCoordinator;
use App\Events\Order\OrderPlaced;
use App\Jobs\Order\AssignDriverToOrder;

class CreateOrderAction implements CreateOrderActionInterface
{
    public function __construct(
        private OrderService $orderService,
        private TransactionCoordinator $transactions,
    ) {}

    public function execute(CreateOrderDto $dto): Order
    {
        return $this->transactions->execute(function () use ($dto) {
            $this->orderService->validateOrderCreation($dto);
            $order = $this->orderService->create($dto);

            event(new OrderPlaced($order));
            AssignDriverToOrder::dispatch($order)->delay(now()->addSeconds(5));

            return $order;
        });
    }
}
