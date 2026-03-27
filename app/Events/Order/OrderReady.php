<?php
namespace App\Events\Order;

class OrderReady
{
    public function __construct(public $order) {}
}
