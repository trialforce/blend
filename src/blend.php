<?php

/* Active php gzip */
ini_set('zlib.output_compression', 'On');

//System constants
define('DS', '/'); //DIRECTORY_SEPARATOR
define('NOW', date('d/m/Y H:i:s'));

require 'autoload.php';

/**
 * Adjust path to system bar
 *
 * @param string $path
 * @return string
 */
function adjusthPath($path)
{
    return str_replace(array('\\', '/'), DS, $path);
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
 * Make a Simple js toast.
 *
 * Type valid values:
 * NULL
 * danger
 * primary
 * info
 * alert
 * success
 *
 * @param string $message toast message, can be html
 * @param string $type a custom css type, in case a extra class in css.
 * @param int $duration default 4000 mileseconds
 */
function toast($message = NULL, $type = NULL, $duration = 4000)
{
    $messageParsed = \View\Script::treatStringToJs($message);

    //play error sound
    if (stripos(' ' . $type, 'danger') > 0)
    {
        \View\Audio::playSoundOnce('theme/audio/error.mp3');
    }

    \App::addJs("toast('{$messageParsed}', '{$type}', {$duration} )");
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

//polyfill por php < 7.0
if (!function_exists('is_iterable'))
{

    /**
     * Verify if an element is iterable
     * @param mixed $obj
     * @return bool
     */
    function is_iterable($obj)
    {
        return is_array($obj) || (is_object($obj) && $obj instanceof \Traversable);
    }

}

/**
 * Exception to user, do not log in file
 */
class UserException extends Exception
{

}
