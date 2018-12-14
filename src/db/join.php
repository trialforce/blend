<?php

namespace Db;

class Join
{

    protected $type;
    protected $tableName;
    protected $alias;
    protected $on;

    public function __construct($type, $tableName, $on, $alias = NULL)
    {
        $this->setType($type);
        $this->setTableName($tableName);
        $this->setOn($on);
        $this->setAlias($alias);
    }

    public function getType()
    {
        return $this->type;
    }

    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    public function setTableName($tableName)
    {
        $this->tableName = $tableName;
        return $this;
    }

    function getTableName()
    {
        return $this->tableName;
    }

    public function getAlias()
    {
        return $this->alias;
    }

    public function getOn()
    {
        return $this->on;
    }

    public function setAlias($alias)
    {
        $this->alias = $alias;
        return $this;
    }

    public function setOn($on)
    {
        $this->on = $on;
        return $this;
    }

    function getSql()
    {
        $alias = $this->alias ? ' AS ' . $this->alias . ' ' : ' ';
        return strtoupper($this->type) . ' JOIN ' . $this->tableName . $alias . ' ON ' . $this->on;
    }

}
