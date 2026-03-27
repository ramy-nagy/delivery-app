<?php
namespace App\Events\Order;

class OrderDelivered
{
    public function __construct(public $order) {}
}
