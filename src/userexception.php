<?php

/**
 * Exception to user, do not log in file
 */
class UserException extends BlendException
{
    public function __construct(string $message = "", int $code = 0, ?Throwable $previous = null, mixed $data = null)
    {
        parent::__construct($message, $code, $previous, $data);
        $this->setDefaultLog(false);
    }
}