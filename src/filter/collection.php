<?php

namespace Filter;

use DataHandle\Request;

/**
 * A filter, based on a collection
 *
 */
class Collection extends \Filter\Text
{

    /**
     *
     * @var \Db\Collection
     */
    protected $collection;

    public function __construct(\Component\Grid\Column $column, $collection, $filterType = NULL)
    {
        parent::__construct($column, NULL, $filterType);
        $this->setCollection($collection);
        $this->setDefaultCondition(self::COND_EQUALS);
    }

    public function getCollection()
    {
        return $this->collection;
    }

    public function setCollection($collection)
    {
        $this->collection = $collection;
        return $this;
    }

    public function getValue()
    {
        $field = new \View\Select($this->getValueName(), $this->collection, $this->getFilterValue(), 'filterInput reference');
        $field->onPressEnter("$('#buscar').click()");

        return $field;
    }

    public function getCondition()
    {
        $conditionName = $this->getConditionName();

        $options[self::COND_EQUALS] = 'Igual';
        $options[self::COND_NOT_EQUALS] = 'Diferente';
        $options[self::COND_NULL_OR_EMPTY] = 'Nulo ou vazio';

        $select = new \View\Select($conditionName, $options, $this->getConditionValue(), 'filterCondition');
        $this->getCondJs($select);

        return $select;
    }

    public function getDbCond()
    {
        $column = $this->getColumn();
        $columnName = $column->getSql();
        $filterName = $this->getValueName();
        $conditionValue = $this->getConditionValue();
        $filterValue = $this->getFilterValue();
        $hasFilter = ($filterValue || $filterValue == 0);

        if ($conditionValue)
        {
            if ($conditionValue == self::COND_EQUALS && $hasFilter)
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
            else if ($conditionValue == self::COND_NOT_EQUALS && $hasFilter)
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
            else if ($conditionValue == self::COND_NULL_OR_EMPTY)
            {
                return new \Db\Cond('(' . $columnName . ' IS NULL OR ' . $columnName . ' = \'\' )', NULL, \Db\Cond::COND_AND, $this->getFilterType());
            }
        }
        //fallback
        else if ($filterValue || $filterValue === '0')
        {
            return new \Db\Cond($columnName . ' = ?', $filterValue . '', \Db\Cond::COND_AND, $this->getFilterType());
        }
    }

}
