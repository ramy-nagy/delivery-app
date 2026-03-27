<?php
namespace App\Domain\Orders\Services;

use App\Domain\Orders\Specifications\PendingOrdersSpecification;
use App\Domain\Orders\Specifications\OrdersByRestaurantSpecification;
use App\Domain\Orders\Repositories\OrderRepositoryInterface;

class RestaurantOrderService
{
    public function __construct(
        private OrderRepositoryInterface $orders
    ) {}

    public function getPendingOrders(int $restaurantId)
    {
        $spec = (new PendingOrdersSpecification())
            ->and(new OrdersByRestaurantSpecification($restaurantId));

        return $this->orders->matching($spec);
    }
}
