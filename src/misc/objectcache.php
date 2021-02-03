<?php

namespace Misc;

/**
 * Simple global object cache
 * The cache works from ONE request
 */
class ObjectCache
{

    static private $cache;

    public static function set($group, $identifier, $value = null)
    {
        $group = trim($group);
        $identifier = trim($identifier);

        self::$cache[$group][$identifier] = $value;
    }

    public static function exists($group, $identifier)
    {
        $group = trim($group);
        $identifier = trim($identifier);
        $itens = self::$cache[$group];
        return isset(self::$cache[$group]) && isset(self::$cache[$group][$identifier]);
    }

    public static function get($group, $identifier)
    {
        $group = trim($group);
        $identifier = trim($identifier);

        if (isset(self::$cache[$group]) && isset(self::$cache[$group][$identifier]))
        {
            return self::$cache[$group][$identifier];
        }
    }

}
