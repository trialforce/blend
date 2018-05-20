<?php

namespace DataHandle;

/**
 * Simple class to manage Request super global
 */
class Request extends DataHandle
{

    /**
     * Construct request
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public function __construct()
    {
        parent::__construct($_REQUEST);
    }

    /**
     * Set request var
     *
     * @param string $var
     * @param mixed $value
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public function setVar($var, $value)
    {
        parent::setVar($var, $value);

        $_REQUEST[$var] = $value;
    }

    /**
     * Return singleton of \Request
     *
     * @return \Request
     */
    public static function getInstance()
    {
        return parent::getInstance();
    }

    /**
     * Get all data and mount a URL with it
     *
     * @return string
     */
    public function mountUri()
    {
        $vars = get_object_vars($this);

        if (is_array($vars))
        {
            foreach ($vars as $line => $info)
            {
                if ($line != 'PHPSESSID')
                {
                    $uri[] = $line . '=' . $info;
                }
            }
        }

        $url = '/?' . implode('&', $uri);

        return $url;
    }

    public static function getArray($param1, $param2)
    {
        if (isset($_REQUEST[$param1]) && $_REQUEST[$param1][$param2])
        {
            return $_REQUEST[$param1][$param2];
        }

        return NULL;
    }

}