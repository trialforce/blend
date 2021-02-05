<?php

namespace Db;

/**
 * Create a relation between two models/tables
 */
class Relation
{

    const TYPE_OTHER = 1;
    const TYPE_FOREIGN_KEY = 1;
    const TYPE_CHILD = 1;

    /**
     * Relation type
     * @var int
     */
    protected $type = self::TYPE_OTHER;

    /**
     * Relation sql
     * @var string
     */
    protected $sql;

    /**
     * Model name
     *
     * @var string
     */
    protected $modelName;

    public function __construct($modelName, $sql, $type = self::TYPE_OTHER)
    {
        $this->setType($type);
        $this->setModelName($modelName);
        $this->setSql($sql);
    }

    public function getType()
    {
        return $this->type;
    }

    public function getSql()
    {
        return $this->sql;
    }

    public function getModelName()
    {
        return $this->modelName;
    }

    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    public function setSql($sql)
    {
        $this->sql = $sql;
        return $this;
    }

    public function setModelName($modelName)
    {
        $this->modelName = $modelName;
        return $this;
    }

}
