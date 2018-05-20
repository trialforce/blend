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

        $options[''] = 'Sim e não (Ambos)';
        $options['t'] = 'Sim';
        $options['f'] = 'Não';
        $options['n'] = 'Nulo';
        $options['nn'] = 'Não nulo';

        $value = $this->parseValue(Request::get($columnValue));

        return new \View\Select($columnValue, $options, $value, 'small filterCondition');
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
        $filterValue = $this->parseValue(Request::get($filterName));
        $cond = NULL;

        if ($filterValue == 't')
        {
            $cond = new \Db\Cond($this->getColumn()->getName() . ' = 1', NULL, \Db\Cond::COND_AND, $this->getFilterType());
        }
        else if ($filterValue == 'f')
        {
            $cond = new \Db\Cond($this->getColumn()->getName() . ' = 0', NULL, \Db\Cond::COND_AND, $this->getFilterType());
        }
        else if ($filterValue == 'n')
        {
            $cond = new \Db\Cond($this->getColumn()->getName() . ' IS NULL', NULL, \Db\Cond::COND_AND, $this->getFilterType());
        }
        else if ($filterValue == 'nn')
        {
            $cond = new \Db\Cond($this->getColumn()->getName() . ' IS NOT NULL', NULL, \Db\Cond::COND_AND, $this->getFilterType());
        }

        return $cond;
    }

}