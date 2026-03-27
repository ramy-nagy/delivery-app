<?php

namespace App\Application\Orders\Contracts;

use App\Application\Orders\Dto\CreateOrderDto;
use App\Domain\Orders\Models\Order;

interface CreateOrderActionInterface
{
    public function execute(CreateOrderDto $dto): Order;
}
