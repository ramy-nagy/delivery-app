<?php

namespace App\Domain\Orders\Exceptions;

use RuntimeException;

class OrderAlreadyCancelledException extends RuntimeException
{
    public function __construct(string $message = 'Order is already cancelled')
    {
        parent::__construct($message);
    }
}
