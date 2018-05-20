<?php

namespace DataHandle;

/**
 * Simple class to manage cookies super global
 */
class Cookie extends DataHandle
{

    /**
     * Construct the cookie
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public function __construct()
    {
        parent::__construct($_COOKIE);
    }

    /**
     * Set cookie variable
     *
     * @param string $var
     * @param mixed $value
     */
    public function setVar($var, $value)
    {
        parent::setVar($var, $value);
        setcookie($var, $value, time() + 3600, "/", '', false, false);
    }

}