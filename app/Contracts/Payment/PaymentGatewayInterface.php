<?php
namespace App\Contracts\Payment;

interface PaymentGatewayInterface
{
    public function charge($amount, $details);
    public function refund($transactionId, $amount);
}
