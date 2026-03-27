<?php
namespace App\Events\Order;

class OrderAccepted
{
    public function __construct(public $order) {}
}
