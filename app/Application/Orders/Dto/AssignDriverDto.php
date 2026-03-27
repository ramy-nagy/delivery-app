<?php

namespace App\Application\Orders\Dto;

class AssignDriverDto
{
    public function __construct(public readonly int $orderId) {}
}
