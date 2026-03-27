<?php

namespace App\Application\Orders\Dto;

class TrackOrderDto
{
    public function __construct(public readonly int $orderId) {}
}
