<?php

define('BLEND_PATH', dirname(__FILE__));

/**
 * Autoload of the framework
 *
 * @param string $class
 */
function autoloadBlend($class)
{
    $file = BLEND_PATH . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, strtolower($class)) . '.php';

    if (is_file($file))
    {
        require $file;
    }
}

spl_autoload_register('autoloadBlend');