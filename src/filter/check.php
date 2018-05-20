<?php

namespace Filter;
use DataHandle\Request;

/**
 * Check filter
 *
 * @author eduardo
 */
class Check extends \Filter\Text
{

    public function getCondition()
    {
        return NULL;
    }

    public function getValue()
    {
        $columnValue = $this->getValueName();

        return new \View\Ext\CheckboxDb($columnValue, 1, Request::get($columnValue) == 1, 'small filterCondition');
    }

    public function getDbCond()
    {
        return new \Db\Cond($this->getColumn()->getName() . ' = 1');
    }

}