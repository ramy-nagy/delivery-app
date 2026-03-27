<?php
namespace App\Broadcasting\Channels;

class OrderChannel
{
    public function join($user, $orderId)
    {
        // Authorize user to join order channel
    }
}
