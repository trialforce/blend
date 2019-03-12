<?php

namespace Filter;

use DataHandle\Request;

/**
 * Reference field filter
 *
 */
class Reference extends \Filter\Collection
{

    const COND_TEXT = 'text';

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

    public function getCondition()
    {
        $conditionName = $this->getConditionName();

        if ($this->dbColumn->getClass())
        {
            $options[self::COND_TEXT] = 'Texto';
        }

        $options[self::COND_EQUALS] = 'Cód - Igual';
        $options[self::COND_NOT_EQUALS] = 'Cód - Diferente';
        $options[self::COND_NULL_OR_EMPTY] = 'Cód - Nulo ou vazio';

        $conditionValue = Request::get($conditionName) ? Request::get($conditionName) : self::COND_TEXT;

        $select = new \View\Select($conditionName, $options, $conditionValue, 'filterCondition');
        $this->getCondJs($select);

        return $select;
    }

    public function getDbCond()
    {
        $column = $this->getColumn();
        $columnName = $column->getSql();
        $conditionName = $this->getConditionName();
        $filterName = $this->getValueName();
        $conditionValue = Request::get($conditionName);
        $filterValue = Request::get($filterName);

        if ($conditionValue && ($filterValue || $filterValue == 0) && $conditionValue == self::COND_TEXT)
        {
            $dbColumn = $this->dbColumn;
            return new \Db\Where($dbColumn->getReferenceSql(FALSE), 'like', \Db\Where::contains($filterValue));
        }
        else
        {
            return parent::getDbCond();
        }
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
            if ($this->dbColumn->getClass())
            {
                $field = new \View\Input($this->getValueName(), 'texxt', $value, 'filterInput');
            }
            else
            {
                $field = new \View\Ext\ReferenceField($this->dbColumn, $columnValue, $value, $class);
            }
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
