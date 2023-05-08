<?php

namespace App\Exceptions;

use Exception;
use Throwable;

class BusinessException extends Exception
{
    protected string $userMessage;

    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        $this->userMessage = $message;
        parent::__construct($message, $code, $previous);
    }

    public function getUserMessage()
    {
        return $this->userMessage;
    }
}
