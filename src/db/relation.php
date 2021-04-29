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

    /**
     * Table name
     * @var string
     */
    protected $tableName;

    /**
     * Relation label
     * @var string
     */
    protected $label;

    public function __construct($label, $modelName, $sql, $type = self::TYPE_OTHER)
    {
        $this->setLabel($label);
        $this->setType($type);
        $this->setModelName($modelName);
        $this->setSql($sql);

        if (class_exists($modelName))
        {
            $this->setTableName($modelName::getTableName());
        }
    }

    public function getLabel()
    {
        return $this->label;
    }

    public function setLabel($label)
    {
        $this->label = $label;
        return $this;
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

    public function getSql()
    {
        return $this->sql;
    }

    public function setSql($sql)
    {
        $this->sql = $sql;
        return $this;
    }

    public function getModelName()
    {
        return $this->modelName;
    }

    public function setModelName($modelName)
    {
        $this->modelName = $modelName;
        return $this;
    }

    public function getTableName()
    {
        return $this->tableName;
    }

    public function setTableName($tableName)
    {
        $this->tableName = $tableName;
        return $this;
    }

    /**
     * Return a new instance of the model of the relation
     *
     * @return \Db\Model the model
     */
    public function getModel()
    {
        $modelName = $this->getModelName();
        return new $modelName();
    }

}
