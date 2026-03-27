<?php

namespace App\Domain\Orders\Exceptions;

use RuntimeException;

class InvalidOrderStatusTransitionException extends RuntimeException
{
    public function __construct(string $message = 'Invalid order status transition')
    {
        parent::__construct($message);
    }
}
