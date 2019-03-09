<?php

namespace Filter;

use DataHandle\Request;

/**
 * Reference field filter
 *
 */
class Reference extends \Filter\Collection
{

    /**
     *
     * @var \Db\Column
     */
    protected $dbColumn;

    public function __construct(\Component\Grid\Column $column, \Db\Column $dbColumn = NULL, $filterType = NULL)
    {
        parent::__construct($column, NULL, $filterType);
        $this->setDbColumn($dbColumn);
    }

    public function getDbColumn()
    {
        return $this->dbColumn;
    }

    public function setDbColumn($dbColumn)
    {
        $this->dbColumn = $dbColumn;
        return $this;
    }

    public function getValue()
    {
        $columnValue = $this->getValueName();
        $class = 'filterInput reference';
        $value = Request::get($columnValue);

        $formatter = $this->column->getFormatter();

        //add support for a formatter as \Db\ConstantValues
        if ($formatter instanceof \Db\ConstantValues)
        {
            $field = new \View\Select($this->getValueName(), $formatter->getArray(), $value, $class);
        }
        else if ($this->dbColumn->getReferenceField())
        {
            $field = new \View\Ext\ReferenceField($this->dbColumn, $columnValue, $value, $class);
        }
        else
        {
            $cValues = $this->dbColumn->getConstantValues();

            if ($cValues instanceof \Db\ConstantValues)
            {
                $cValues = $cValues->getArray();
            }

            $field = new \View\Select($this->getValueName(), $cValues, $value, $class);
        }

        //$field->setMultiple(true);
        $field->onPressEnter("$('#buscar').click()");

        return $field;
    }

}
