<?php
namespace App\Events\Order;

class OrderCancelled
{
    public function __construct(public $order) {}
}
