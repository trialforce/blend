<?php

namespace Db\Migration;

/**
 * Database Migration version
 * Used when you neeed to create a PHP script as version
 */
abstract class Version
{

    /**
     *
     * @var \Migration\Manager
     */
    protected $migration;

    /**
     *
     * @return \Db\Migration\Manager
     */
    public function getMigration()
    {
        return $this->migration;
    }

    public function setMigration($migration)
    {
        $this->migration = $migration;
        return $this;
    }

    abstract function execute();
}
