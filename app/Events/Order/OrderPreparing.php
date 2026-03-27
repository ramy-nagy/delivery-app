<?php
namespace App\Events\Order;

class OrderPreparing
{
    public function __construct(public $order) {}
}
