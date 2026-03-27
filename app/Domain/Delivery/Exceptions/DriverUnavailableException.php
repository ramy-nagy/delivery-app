<?php

namespace App\Domain\Delivery\Exceptions;

use RuntimeException;

class DriverUnavailableException extends RuntimeException
{
    public function __construct(string $message = 'No driver available')
    {
        parent::__construct($message);
    }
}
