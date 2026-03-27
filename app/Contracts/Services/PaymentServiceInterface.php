<?php
namespace App\Contracts\Services;

interface PaymentServiceInterface
{
    public function processPayment($dto);
    public function refundPayment($dto);
}
