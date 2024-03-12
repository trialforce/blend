<?php

namespace Cache;

/**
 * Caching system using memory (RAM)
 */
class Memory implements \Cache\Service
{
    protected \Cache\ConnInfo $info;
    protected static $cacheData;

    public function __construct(ConnInfo $info)
    {
        $this->info = $info;
        $folder = $this->info->getFolder();

        if (!$folder)
        {
            throw new \Exception('É necessário definir uma pasta para user o cache de memory');
        }

        //start the array
        self::$cacheData[$folder]  = [];
    }

    public function set($key, $value)
    {
        $folder = $this->info->getFolder();
        self::$cacheData[$folder][$key] = $value;
        return true;
    }

    public function get($key)
    {
        $folder = $this->info->getFolder();

        if (isset(self::$cacheData[$folder][$key]))
        {
            return self::$cacheData[$folder][$key];
        }

        return null;
    }

    public function del($key)
    {
        $folder = $this->info->getFolder();

        if (isset(self::$cacheData[$folder][$key]))
        {
            unset(self::$cacheData[$folder][$key]);
        }
    }

    public function exists($key)
    {
        $folder = $this->info->getFolder();

        return isset(self::$cacheData[$folder][$key]);
    }

    public function allKeys()
    {
        $folder = $this->info->getFolder();

        return self::$cacheData[$folder];
    }
}