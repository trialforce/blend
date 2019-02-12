<?php

namespace Filter;

use DataHandle\Request;

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of reference
 *
 * @author eduardo
 */
class Reference extends \Filter\Text
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

    public function getCondition()
    {
        $conditionName = $this->getConditionName();

        $options[self::COND_EQUALS] = 'Igual';
        $options[self::COND_NOT_EQUALS] = 'Diferente';
        $options[self::COND_NULL_OR_EMPTY] = 'Nulo ou vazio';

        $conditionValue = Request::get($conditionName) ? Request::get($conditionName) : self::COND_EQUALS;

        $select = new \View\Select($conditionName, $options, $conditionValue, 'filterCondition');
        $this->getCondJs($select);

        return $select;
    }

    public function getDbCond()
    {
        $column = $this->getColumn();
        $columnName = $column->getName();
        $conditionName = $this->getConditionName();
        $filterName = $this->getValueName();
        $conditionValue = Request::get($conditionName);
        $filterValue = Request::get($filterName);

        if ($conditionValue && ($filterValue || $filterValue == 0))
        {
            if ($conditionValue == self::COND_EQUALS)
            {
                //multiple (array)
                if (is_array($filterValue))
                {
                    //optimize for 1 register
                    if (count($filterValue) == 1)
                    {
                        return new \Db\Cond($columnName . ' = ? ', $filterValue, \Db\Cond::COND_AND, $this->getFilterType());
                    }
                    else
                    {
                        $filterValue = implode("','", Request::get($filterName));
                        $cond = new \Db\Cond($columnName . " IN ( '$filterValue' )", NULL, \Db\Cond::COND_AND, $this->getFilterType());
                        return $cond;
                    }
                }
                else if ($filterValue || $filterValue === '0')
                {
                    return new \Db\Cond($columnName . ' = ?', $filterValue . '', \Db\Cond::COND_AND, $this->getFilterType());
                }
            }
            else if ($conditionValue == self::COND_NOT_EQUALS)
            {
                //multiple (array)
                if (is_array($filterValue))
                {
                    //optimize for 1 register
                    if (count($filterValue) == 1 && strlen($filterValue[0]) > 0)
                    {
                        return new \Db\Cond($columnName . ' != ? ', $filterValue, \Db\Cond::COND_AND, $this->getFilterType());
                    }
                    else if (count($filterValue) > 1)
                    {
                        $filterValue = implode("','", Request::get($filterName));
                        return new \Db\Cond($columnName . " NOT IN ( '$filterValue' )", NULL, \Db\Cond::COND_AND, $this->getFilterType());
                    }
                }
                else if ($filterValue || $filterValue === '0')
                {
                    return new \Db\Cond($columnName . ' != ?', $filterValue . '', \Db\Cond::COND_AND, $this->getFilterType());
                }
            }
        }
        else if ($filterValue || $filterValue === '0')
        {
            return new \Db\Cond($columnName . ' = ?', $filterValue . '', \Db\Cond::COND_AND, $this->getFilterType());
        }
        else if ($conditionValue == self::COND_NULL_OR_EMPTY)
        {
            return new \Db\Cond('(' . $columnName . ' IS NULL OR ' . $columnName . ' = \'\' )', NULL, \Db\Cond::COND_AND, $this->getFilterType());
        }
    }

}
