<?php
namespace App\Application\Payment\Dto;

class ProcessPaymentDto
{
    public int $orderId;
    public float $amount;
    public string $paymentMethod;

    public function __construct(int $orderId, float $amount, string $paymentMethod)
    {
        $this->orderId = $orderId;
        $this->amount = $amount;
        $this->paymentMethod = $paymentMethod;
    }
}
