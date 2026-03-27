<?php
namespace App\Broadcasting\Events;

class OrderStatusBroadcast
{
    public $orderId;
    public $status;

    public function __construct($orderId, $status)
    {
        $this->orderId = $orderId;
        $this->status = $status;
    }
}
