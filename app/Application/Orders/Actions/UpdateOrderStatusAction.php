<?php
namespace App\Application\Orders\Actions;

use App\Application\Orders\Dto\UpdateOrderStatusDto;
use App\Domain\Orders\Models\Order;
use App\Domain\Orders\Services\OrderService;
use App\Domain\Orders\Exceptions\OrderNotFoundException;
use Illuminate\Support\Facades\DB;

class UpdateOrderStatusAction
{
    public function __construct(private OrderService $orderService) {}

    /**
     * Update the status of an order.
     *
     * @param UpdateOrderStatusDto $dto
     * @return Order
     * @throws OrderNotFoundException
     */
    public function handle(UpdateOrderStatusDto $dto): Order
    {
        return DB::transaction(function () use ($dto) {
            $order = $this->orderService->findById($dto->orderId);
            if (!$order) {
                throw new OrderNotFoundException();
            }
            $order = $this->orderService->updateStatus($order, $dto->status);
            return $order;
        });
    }
}
