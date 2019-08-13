<?php

namespace Filter;

use DataHandle\Request;

/**
 * A filter, based on a collection
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

    /**
     * Create a \Filter\Collection, similar to page->createExtraFilter
     *
     * @param \Component\Grid\Column $column
     * @param \Db\Collection $collection
     * @param array $defaultValue
     * @return \Filter\Collection
     */
    public static function create(\Component\Grid\Column $column, $collection, $defaultValue, $defaultCondition = NULL)
    {
        $filter = new \Filter\Collection($column, $collection);
        $filter->setDefaultValue($defaultValue);
        $filter->setDefaultCondition($defaultCondition);

        return $filter;
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

    public function getInputValue($index = 0)
    {
        $field = new \View\Select($this->getValueName() . '[]', $this->collection, $this->getFilterValue($index), 'filterInput reference');
        $field->onPressEnter("$('#buscar').click()");

        return $field;
    }

    public function getConditionList()
    {
        $options = array();
        $options[self::COND_EQUALS] = 'Igual';
        $options[self::COND_NOT_EQUALS] = 'Diferente';
        $options[self::COND_NULL_OR_EMPTY] = 'Vazio';
        $options[self::COND_NOT_NULL_OR_EMPTY] = 'Não vazio';

        return $options;
    }

    public function createWhere($index = 0)
    {
        $column = $this->getColumn();
        $columnName = $column->getSql();
        $filterName = $this->getValueName();
        $conditionValue = $this->getConditionValue($index);
        $filterValue = $this->getFilterValue($index);
        $conditionType = $index > 0 ? \Db\Cond::COND_OR : \Db\Cond::COND_AND;
        $hasFilter = (strlen($filterValue) > 0);

        //no condition selected, does nothing
        if (!$conditionValue)
        {
            return null;
        }

        if ($conditionValue == self::COND_NULL_OR_EMPTY)
        {
            return new \Db\Cond('( (' . $columnName . ') IS NULL OR (' . $columnName . ') = \'\' )', NULL, $conditionType);
        }
        else if ($conditionValue == self::COND_NOT_NULL_OR_EMPTY)
        {
            return new \Db\Cond('( (' . $columnName . ') IS NOT NULL AND (' . $columnName . ') != \'\' )', NULL, $conditionType);
        }
        //no filter selected does nothing
        else if (!$hasFilter)
        {
            return null;
        }

        if ($conditionValue == self::COND_EQUALS)
        {
            //multiple (array)
            if (is_array($filterValue))
            {
                //optimize for 1 register
                if (count($filterValue) == 1)
                {
                    return new \Db\Cond($columnName . ' = ? ', $filterValue, $conditionType);
                }
                else
                {
                    $filterValue = implode("','", Request::get($filterName));
                    $cond = new \Db\Cond($columnName . " IN ( '$filterValue' )", NULL, $conditionType);
                    return $cond;
                }
            }
            else
            {
                return new \Db\Where($columnName, $conditionValue, $filterValue . '', $conditionType);
            }
        }
        else if ($conditionValue == self::COND_NOT_EQUALS)
        {
            $conditionType = \Db\Cond::COND_AND;

            //multiple (array)
            if (is_array($filterValue))
            {
                //optimize for 1 register
                if (count($filterValue) == 1 && strlen($filterValue[0]) > 0)
                {
                    return new \Db\Cond($columnName . ' != ? ', $filterValue, $conditionType);
                }
                else if (count($filterValue) > 1)
                {
                    $filterValue = implode("','", Request::get($filterName));
                    return new \Db\Cond($columnName . " NOT IN ( '$filterValue' )", NULL, $conditionType);
                }
            }
            else if ($filterValue || $filterValue === '0')
            {
                return new \Db\Cond($columnName . ' != ?', $filterValue . '', $conditionType);
            }
        }
        //fallback
        else
        {
            return new \Db\Cond($columnName . ' = ?', $filterValue . '', $conditionType);
        }
    }

}
