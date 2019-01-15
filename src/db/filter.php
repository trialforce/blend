<?php

namespace Db;

/**
 * A filter do criteria part of sql
 *
 */
interface Filter
{

    function getString($first = false);

    function getStringPdo($first = false);

    function getArgs();
}
