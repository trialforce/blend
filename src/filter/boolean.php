<?php

namespace Filter;
use DataHandle\Request;

/**
 * Description of int
 *
 * @author eduardo
 */
class Boolean extends \Filter\Text
{

    public function getCondition()
    {
        return NULL;
    }

    public function getValue()
    {
        $columnValue = $this->getValueName();

        $options['t'] = 'Sim';
        $options['f'] = 'Não';

        $value = $this->parseValue(Request::get($columnValue));

        $input = new \View\Select($columnValue, $options, $value, '');
        $input->onPressEnter("$('#buscar').click()");

        return $input;
    }

    public function parseValue($value)
    {
        if ($value == 1 || $value == 'true')
        {
            $value = 't';
        }

        return $value;
    }

    public function getDbCond()
    {
        $filterName = $this->getValueName();
        $columnName = $this->getColumn()->getName();
        $filterValue = $this->parseValue(Request::get($filterName));
        $cond = NULL;

        if ($filterValue == 't')
        {
            $cond = new \Db\Cond('(' . $columnName . ' = 1 OR ' . $columnName . ' IS NOT NULL)', NULL, \Db\Cond::COND_AND, $this->getFilterType());
        }
        else if ($filterValue == 'f')
        {

            $cond = new \Db\Cond('(' . $columnName . ' = 0 OR ' . $columnName . ' IS NULL)', NULL, \Db\Cond::COND_AND, $this->getFilterType());
        }

        return $cond;
    }

}