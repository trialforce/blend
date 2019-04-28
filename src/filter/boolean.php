<?php

namespace Filter;

use DataHandle\Request;

/**
 * Boolean filter
 */
class Boolean extends \Filter\Text
{

    public function getCondition()
    {
        return NULL;
    }

    public function getInputValue($index = 0)
    {
        $input = new \View\Select($columnValue, $this->getConditionList(), $this->parseValue($this->getFilterValue($index)), 'fullWidth');
        $input->onPressEnter("$('#buscar').click()");

        return $input;
    }

    public function getConditionList()
    {
        $options = array();
        $options['t'] = 'Sim';
        $options['f'] = 'Não';

        return $options;
    }

    public function parseValue($value)
    {
        if ($value == 1 || $value == 'true')
        {
            $value = 't';
        }

        return $value;
    }

    public function createWhere($index = 0)
    {
        $columnName = $this->getColumn()->getName();
        $filterValue = $this->parseValue($this->getFilterValue($index));
        $cond = NULL;

        if ($filterValue == 't')
        {
            $cond = new \Db\Where($columnName, '>=', 1, \Db\Cond::COND_AND, $this->getFilterType());
        }
        else if ($filterValue == 'f')
        {

            $cond = new \Db\Where('(' . $columnName . ' = 0 OR ' . $columnName . ' IS NULL)', NULL, NULL, \Db\Cond::COND_AND, $this->getFilterType());
        }

        return $cond;
    }

}
