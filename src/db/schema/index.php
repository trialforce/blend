<?php

namespace Db\Schema;

/**
 * Database index
 */
class Index
{

    const TYPE_PRIMARY = 'primary';
    const TYPE_UNIQUE = 'unique';
    const TYPE_KEY = 'key';

    protected $name;
    protected $type = \Db\Schema\Index::TYPE_KEY;
    protected $columns = [];
    protected $errorMessage;

    public function __construct($name, $type, $columns, $errorMessage = null)
    {
        $this->setName($name);
        $this->setType($type);
        $this->setColumss($columns);
        $this->setErrorMessage($errorMessage);
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
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

    public function getColumss()
    {
        return $this->columns;
    }

    public function setColumss($columns)
    {
        $this->columns = $columns;
        return $this;
    }

    public function addColumn($columnName)
    {
        $this->columns[] = $columnName;
    }

    public function getErrorMessage()
    {
        return $this->errorMessage;
    }

    public function setErrorMessage($errorMessage)
    {
        $this->errorMessage = $errorMessage;

        $indexData = \Log::getIndexData();
        $indexData->addIndex($this->getName(), $this->getErrorMessage());

        return $this;
    }

}
