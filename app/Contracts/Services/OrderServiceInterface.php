<?php
namespace App\Contracts\Services;

interface OrderServiceInterface
{
    public function createOrder($dto);
    public function updateOrderStatus($dto);
}
