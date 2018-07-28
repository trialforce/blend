<?php

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