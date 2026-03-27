<?php

namespace App\Contracts\Repositories;

use App\Domain\Orders\Models\Order;

/**
 * Application-level contract; mirrors persistence operations used outside the domain layer.
 */
interface OrderRepositoryInterface
{
    public function find(int $id): ?Order;

    public function save(Order $order): Order;
}
