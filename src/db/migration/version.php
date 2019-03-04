<?php

namespace Db\Migration;

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
