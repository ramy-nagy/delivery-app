<?php
namespace App\Events\Order;

class OrderPlaced
{
    public function __construct(public $order) {}
}
