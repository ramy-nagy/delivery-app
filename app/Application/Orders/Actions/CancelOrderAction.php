<?php
namespace App\Application\Orders\Actions;

use App\Application\Orders\Dto\CancelOrderDto;
use App\Domain\Orders\Models\Order;
use App\Domain\Orders\Services\OrderService;
use App\Domain\Orders\Exceptions\OrderAlreadyCancelledException;
use App\Domain\Orders\Exceptions\OrderNotFoundException;
use App\Enums\OrderStatus;
use Illuminate\Support\Facades\DB;

class CancelOrderAction
{
    public function __construct(private OrderService $orderService) {}

    /**
     * Cancel an order by ID and reason.
     *
     * @param CancelOrderDto $dto
     * @return Order
     * @throws OrderNotFoundException
     * @throws OrderAlreadyCancelledException
     */
    public function handle(CancelOrderDto $dto): Order
    {
        return DB::transaction(function () use ($dto) {
            $order = $this->orderService->findById($dto->orderId);
            if (!$order) {
                throw new OrderNotFoundException();
            }
            if ($order->status === OrderStatus::CANCELLED) {
                throw new OrderAlreadyCancelledException();
            }
            $this->orderService->assertCustomerCanCancel($order);
            $order = $this->orderService->cancel($order, $dto->reason);
            return $order;
        });
    }
}
