<?php

namespace Cache;

class Cache
{
    protected static $cache;
    protected static $connInfo;

    public static function set($key, $value, $info = 'default')
    {
        $cache = self::getInstance($info);
        return $cache->set($key, $value);
    }

    public static function get($key, $info = 'default')
    {
        $cache = self::getInstance($info);
        return $cache->get($key);
    }

    public static function del($key, $info = 'default')
    {
        $cache = self::getInstance($info);
        return $cache->del($key);
    }

    public static function exists($key, $info = 'default')
    {
        $cache = self::getInstance($info);
        return $cache->exists($key);
    }

    public static function allKeys( $info = 'default')
    {
        $cache = self::getInstance($info);
        return $cache->allKeys();
    }

    public static function addConnInfo(\Cache\ConnInfo $info)
    {
        self::$connInfo[$info->getId()] = $info;
    }

    /**
     * @param $id
     * @param $throw
     * @return \Cache\ConnInfo|null
     * @throws \Exception
     */
    public static function getConnInfo($id = 'default', $throw = TRUE)
    {
        $id = is_null($id) ? 'default' : $id;

        //create one default conninfo to keep the old code working
        if ($id=='default' && !isset(self::$connInfo[$id]))
        {
            new \Cache\Conninfo('default',\Cache\ConnInfo::TYPE_STORAGE, 'cache');
        }

        //create one default conninfo to memory cache
        if ($id=='memory' && !isset(self::$connInfo[$id]))
        {
            new \Cache\Conninfo('memory',\Cache\ConnInfo::TYPE_MEMORY, 'cache');
        }

        if (isset(self::$connInfo[$id]))
        {
            return self::$connInfo[$id];
        }
        else if ($throw)
        {
            throw new \Exception("Informações de cache '$id' não encontradas.");
        }

        return null;
    }

    /**
     * @param $id
     * @return \Cache\Service
     * @throws \Exception
     */
    public static function getInstance($id = 'default')
    {
        $info = self::getConnInfo($id);

        if (!isset(self::$cache[$id]))
        {
            try
            {
                $className = 'cache\\'.$info->getType();
                self::$cache[$id] = new $className($info);
            }
            catch (\Exception $e)
            {
                \Log::exception($e);
                throw new \Exception('O sistema não está conseguindo se conectar ao servidor de CACHE. Por favor tente novamente mais tarde.');
            }
        }

        return self::$cache[$id];
    }
}