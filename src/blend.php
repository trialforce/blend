<?php

/* Active php gzip */
ini_set('zlib.output_compression', 'On');

/**
 * Directory separaror
 * @deprecated since version 28/07/2018
 */
define('DS', '/');
/**
 * Current timestamp in brazilial format
 * @deprecated since version 28/07/2018
 */
define('NOW', date('d/m/Y H:i:s'));

require 'autoload.php';

/**
 * Adjust path to system bar
 * @deprecated since version 28/07/2018
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
 * Or any other css class
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

/**
 * Exception to user, do not log in file
 */
class UserException extends Exception
{

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
 * Return an element by it's id
 * Alias function to \View\View::getDom()->byId
 * @param string $id
 * @return \View\View
 */
function byId($id)
{
    return \View\View::getDom()->byId($id);
}

if (!function_exists('filePath'))
{

    function filePath($class, $extension = 'php')
    {
        $class = strtolower(str_replace('\\', DIRECTORY_SEPARATOR, $class));
        return APP_PATH . DIRECTORY_SEPARATOR . $class . '.' . $extension;
    }

}

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