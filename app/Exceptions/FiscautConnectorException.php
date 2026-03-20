<?php

namespace App\Exceptions;

use Exception;

class FiscautConnectorException extends Exception
{
    public function __construct(string $message = '', ?string $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
