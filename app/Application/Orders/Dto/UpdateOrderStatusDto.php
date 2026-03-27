<?php

namespace App\Application\Orders\Dto;

use App\Enums\OrderStatus;

class UpdateOrderStatusDto
{
    public function __construct(
        public readonly int $orderId,
        public readonly OrderStatus $status,
    ) {}
}
