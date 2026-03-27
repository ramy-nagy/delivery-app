<?php

namespace App\Domain\Orders\Exceptions;

use RuntimeException;

class OrderNotFoundException extends RuntimeException
{
    public function __construct(string $message = 'Order not found')
    {
        parent::__construct($message);
    }
}
