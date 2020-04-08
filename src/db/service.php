<?php

namespace Db;

/**
 * Generic service class to execute some kind of service
 */
abstract class Service
{

    /**
     * Execute the servive
     * @return mixed the return type depends on the service implementation
     */
    abstract function execute();

    /**
     * Static Construct
     *
     * @return \Db\Service
     */
    public static function create()
    {
        $class = get_called_class();
        return new $class();
    }

    /**
     * Instance the service and execute
     *
     * @return mixed
     */
    public static function executeNow()
    {
        $class = get_called_class();
        $service = new $class();

        return $service->execute();
    }

}
