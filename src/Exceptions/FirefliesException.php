<?php

namespace Sleemkeen\Fireflies\Exceptions;

use Exception;

class FirefliesException extends Exception
{
    protected $errors;

    public function __construct($message = '', $errors = [], $code = 0)
    {
        parent::__construct($message, $code);
        $this->errors = $errors;
    }

    public function getErrors()
    {
        return $this->errors;
    }
} 