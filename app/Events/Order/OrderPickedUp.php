<?php
namespace App\Events\Order;

class OrderPickedUp
{
    public function __construct(public $order) {}
}
