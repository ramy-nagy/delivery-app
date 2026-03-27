<?php
namespace App\Application\Orders\Actions;

use App\Application\Orders\Dto\TrackOrderDto;
use App\Domain\Orders\Models\Order;
use App\Domain\Orders\Services\OrderService;

class TrackOrderAction
{
    public function __construct(private OrderService $orderService) {}

    /**
     * Track an order by ID.
     *
     * @param TrackOrderDto $dto
     * @return Order|null
     */
    public function handle(TrackOrderDto $dto): ?Order
    {
        return $this->orderService->findById($dto->orderId);
    }
}
