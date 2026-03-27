<?php
namespace App\Application\Orders\Dto;

class CancelOrderDto
{
    public int $orderId;
    public ?string $reason;

    public function __construct(int $orderId, ?string $reason = null)
    {
        $this->orderId = $orderId;
        $this->reason = $reason;
    }
}
