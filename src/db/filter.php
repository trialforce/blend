<?php

namespace Db;

/**
 * A filter do criteria part of sql
 *
 */
interface Filter
{

    /**
     * Get sql string without parameters
     *
     * @param boolean $first
     * @return string
     */
    function getString($first = false);

    /**
     * Simulate a string like if PDO was mounting it.
     * Or, replace the ? with the values
     *
     * @param boolean $first
     * @return string
     */
    function getStringPdo($first = false);

    /**
     * Return the args of the filter
     */
    function getArgs();
}
