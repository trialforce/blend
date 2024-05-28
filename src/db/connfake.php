<?php

namespace Db;

/**
 * This is a fake database connection that does nothing.
 * Used for optional databases
 */
class ConnFake extends Conn
{
    public function __construct(\Db\ConnInfo $info)
    {
        $this->id = $info->getId();
    }

    public function getServerInfo()
    {
        return null;
    }

    public static function getLastRet()
    {
        return null;
    }

    public static function getLastSql()
    {
        return null;
    }

    public function execute($sql, $args = NULL, $logId = null)
    {
        return null;
    }

    public function exec($sql)
    {
        return null;
    }

    public function query($sql, $args = array(), $class = NULL, $logId = null)
    {
        return null;
    }

    public function findOne($sql, $args = array(), $class = NULL, $logId = null)
    {
        return null;
    }

    public function lastInsertId()
    {
        return null;
    }

    public function beginTransaction()
    {
        return null;
    }

    public function inTransaction()
    {
        return null;
    }

    public function commit()
    {
        return null;
    }

    public function rollBack()
    {
        return null;
    }


}