<?php

namespace Db;

/**
 * Represents a join in the query
 */
class Join
{

    protected $type;
    protected $tableName;
    protected $alias;
    protected $on;

    /**
     * Construct the join
     * @param string $type left, right, inner, full etc
     * @param string $tableName the name of the table
     * @param string $on the relation between the two tables
     * @param string $alias as lias to the joined table, if needed
     */
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

    function __toString()
    {
        return $this->getSql();
    }

}
