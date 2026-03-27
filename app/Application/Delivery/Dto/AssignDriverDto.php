<?php
namespace App\Application\Delivery\Dto;

class AssignDriverDto
{
    public int $orderId;
    public int $driverId;

    public function __construct(int $orderId, int $driverId)
    {
        $this->orderId = $orderId;
        $this->driverId = $driverId;
    }
}
