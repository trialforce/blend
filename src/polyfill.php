<?php

/***
 * This file is used to make blend work with some older versions of PHP.
 */

//8.0 function
if (!function_exists('str_starts_with'))
{

    function str_starts_with($haystack, $needle)
    {
        $length = strlen($needle);
        return substr($haystack, 0, $length) === $needle;
    }

}

//8.0 function
if (!function_exists('str_ends_with'))
{

    function str_ends_with($haystack, $needle)
    {
        $length = strlen($needle);

        if (!$length)
        {
            return true;
        }

        return substr($haystack, -$length) === $needle;
    }

}

//7.0 function
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

//Create mb_str_pad function if not exists, for old php compatibility
if (!function_exists('mb_str_pad'))
{

    function mb_str_pad($input, $padLength, $padString, $padStyle, $encoding = "UTF-8")
    {
        return str_pad($input, strlen($input) - mb_strlen($input, $encoding) + $padLength, $padString, $padStyle);
    }

}