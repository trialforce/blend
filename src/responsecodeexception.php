<?php

/**
 * Exception to register on response code, usefull on API's
 */
class ResponseCodeException extends \BlendException
{
    public function __construct(string $message = "", int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->setDefaultLog(false);
    }
}