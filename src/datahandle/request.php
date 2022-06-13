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
        //RESOLVE A PHP BUG see https://www.php.net/variables.external
        $var = str_replace('.', '_', $var);
        parent::setVar($var, $value);

        $_REQUEST[$var] = $value;
    }

    public function getVar($var)
    {
        $var = str_replace('.', '_', $var);
        return parent::getVar($var);
    }

    public static function get($var)
    {
        $var = str_replace('.', '_', $var);
        return parent::get($var);
    }

    public static function getDefault($var, $defaultValue)
    {
        $var = str_replace('.', '_', $var);
        return parent::getDefault($var, $defaultValue);
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

        $uri = [];

        if (is_array($vars))
        {
            foreach ($vars as $line => $info)
            {
                if ($line == 'PHPSESSID')
                {
                    continue;
                }

                $value = $info;

                $valid = true;

                if (is_array($info))
                {
                    if (isset($info[0]))
                    {
                        $value = $info[0];

                        if (is_array($value))
                        {
                            $valid = false;
                        }
                    }
                    else
                    {
                        // empty array
                        $valid = false;
                    }
                }

                if ($valid)
                {
                    $uri[] = $line . '=' . $value;
                }
            }
        }

        $url = '/?' . implode('&', $uri);

        return $url;
    }

    public static function getArray($param1, $param2)
    {
        if (isset($_REQUEST[$param1]) && isset($_REQUEST[$param1][$param2]) && $_REQUEST[$param1][$param2])
        {
            return $_REQUEST[$param1][$param2];
        }

        return NULL;
    }

}
