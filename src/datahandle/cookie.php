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
        parent::__construct();

        //avoid setData and setVar to not messing with cookies
        $cookies = $_COOKIE;

        foreach ($cookies as $key => $value)
        {
            $this->$key = $value;
        }
    }

    /**
     * Set cookie variable
     *
     * @param string $var
     * @param mixed $value
     */
    public function setVar($var, $value)
    {
        if (headers_sent())
        {
            return false;
        }

        parent::setVar($var, $value);

        setcookie($var, $value, [
            'expires' => time() + 3600,
            'path' => '/',
            'domain' => null,
            'samesite' => 'strict',
            'secure' => true,
            'httponly' => false,
        ]);

        return true;
    }

}
