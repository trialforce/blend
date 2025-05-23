<?php

require 'autoload.php';

/**
 *
 * This file is mainly used to active the framework.
 * And beyond that it has some generic purpose functions
 *
 */
/* Active php gzip */
ini_set('zlib.output_compression', 'On');

//@todo this is not the right place for it
//grow session security
if (session_status() == PHP_SESSION_NONE)
{
    //avoid autoload
    require_once BLEND_PATH.'/datahandle/datahandle.php';
    require_once BLEND_PATH.'/datahandle/server.php';
    ini_set('session.cookie_lifetime', 0);
    ini_set('session.sid_length', 48);
    ini_set('session.use_cookies', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.use_strict_mode', 1);
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_samesite', 'Lax');

    if (\DataHandle\Server::getInstance()->isHttps())
    {
        ini_set('session.cookie_secure', 1);
    }
}

/**
 * Reproduces javascript function alert, to be called from PHP
 *
 * @param string $message the message itself
 *
 */
function alert($message)
{
    \App::addJs('alert(\'' . $message . '\');');
}

/**
 * Glob recursive
 * Does not support flag GLOB_BRACE
 *
 * @param string $pattern
 * @param int $flags
 * @return array
 */
function globRecursive($pattern, $flags = 0)
{
    $files = glob($pattern, $flags);

    foreach (glob(dirname($pattern) . '/*', GLOB_ONLYDIR | GLOB_NOSORT) as $dir)
    {
        $files = is_array($files) ? $files : array();
        $fileRecursive = globRecursive($dir . '/' . basename($pattern), $flags);
        $fileRecursive = is_array($fileRecursive) ? $fileRecursive : array();
        $files = array_merge($files, $fileRecursive);
    }

    return $files;
}

/**
 * Verify if an element is iterable
 * @param mixed $obj
 * @return bool
 */
function isIterable($obj)
{
    return is_array($obj) || is_object($obj);
}

/**
 * Verify if an element is an array or implements Countable.
 * There is a function is_countable, but only in PHP 7.3.
 * @param mixed $obj
 * @return bool
 */
function isCountable($obj)
{
    return $obj instanceof \Countable || is_array($obj);
}

/**
 * Return an element by it's id
 * Alias function to \View\View::getDom()->byId
 * @param string $id
 * @return \View\View
 */
function byId($id)
{
    return \View\View::getDom()->byId($id);
}

/**
 * Parse/explode a url considering subdomain and domain
 * @param string $url url
 * @return array|null explode array
 */
function parseUrl($url)
{
    //gambiarra temporária pra resolver porque não funcionava com .com.br
    $url = str_replace('.br', '', $url);
    $out = null;
    $r = "^(?:(?P<scheme>\w+)://)?";
    $r .= "(?:(?P<login>\w+):(?P<pass>\w+)@)?";
    $r .= "(?P<host>(?:(?P<subdomain>[\w\.]+)\.)?" . "(?P<domain>\w+\.(?P<extension>\w+)))";
    $r .= "(?::(?P<port>\d+))?";
    $r .= "(?P<path>[\w/]*/(?P<file>\w+(?:\.\w+)?)?)?";
    $r .= "(?:\?(?P<arg>[\w=&]+))?";
    $r .= "(?:#(?P<anchor>\w+))?";
    $r = "!$r!";

    preg_match($r, $url, $out);

    return $out;
}

//default blend function to find any file, can be overwritten in yout index.php
if (!function_exists('filePath'))
{

    function filePath($class, $extension = 'php')
    {
        $classParsed = strtolower(str_replace('\\', '/', $class));
        $admPath = defined('ADM_PATH') ? ADM_PATH : APP_PATH;
        return $admPath. '/' . $classParsed . '.' . $extension;
    }

}

//default blend function to load any file, can be overwritten in yout index.php
if (!function_exists('loadFile'))
{

    function loadFile($class)
    {
        $fileName = filePath($class, 'php');

        if (is_file($fileName))
        {
            require $fileName;
        }
    }

    spl_autoload_register('loadFile');
}


if (!function_exists('toast'))
{

    /**
     * Make a Simple js toast.
     *
     * Type valid values:
     * NULL
     * danger
     * primary
     * info
     * alert
     * success
     * Or any other css class
     *
     * @param string $message toast message, can be html
     * @param string $type a custom css type, in case a extra class in css.
     * @param int $duration default 4000 mileseconds
     * @throws Exception
     */
    function toast($message = NULL, $type = NULL, $duration = 4000)
    {
        //little control to improved debug using toast
        if (is_object($message))
        {
            $message = \Disk\Json::encode($message);
        }

        $messageParsed = \View\Script::treatStringToJs($message);
        \App::addJs("toast('$messageParsed', '$type', $duration )");
        return false;
    }

}

//create the internacionalization function
if (!function_exists('_'))
{
    function _($text)
    {
        return $text;
    }
}

//create the function that don't exists on php-fpm/shell
if (!function_exists('getallheaders'))
{
    function getallheaders()
    {
        $headers = [];

        foreach ($_SERVER as $name => $value)
        {
            if (str_starts_with($name, 'HTTP_'))
            {
                $property = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))));
                $headers[$property] = $value;
            }
        }

        return $headers;
    }
}

/**
 * Exception to user, do not log in file
 */
class UserException extends Exception
{

}

/**
 * Exception to register on response code, util on API's
 */
class ResponseCodeException extends \Exception
{

}