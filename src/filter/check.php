<?php

namespace Filter;

/**
 * Check filter
 * FIXME where is this used???
 *
 */
class Check extends \Filter\Text
{

    public function getCondition()
    {
        return NULL;
    }

    public function getInputValue($index)
    {
        $columnValue = $this->getValueName();

        return new \View\Ext\CheckboxDb($columnValue, 1, $this->getFilterValue($index) == 1, 'small filterCondition');
    }

    public function getDbCond()
    {
        return new \Db\Where($this->getColumn()->getName(), '=', '1');
    }

}
