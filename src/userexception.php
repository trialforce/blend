<?php

/**
 * Exception to user, do not log in file
 */
class UserException extends BlendException
{
    public function __construct(string $message = "", int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->setDefaultLog(false);
    }
}