<?php

namespace Filter;

use DataHandle\Request;

/**
 * Boolean filter
 */
class Boolean extends \Filter\Text
{

    const COND_TRUE = 't';
    const COND_FALSE = 'f';

    public function getCondition()
    {
        return NULL;
    }

    public function getInputValue($index = 0)
    {
        $input = new \View\Select($this->getValueName() . '[]', $this->getConditionList(), $this->parseValue($this->getFilterValue($index)), 'filterInput fullWidth');
        $input->onPressEnter("$('#buscar').click()");

        return $input;
    }

    public function getConditionList()
    {
        $options = array();
        $options[self::COND_TRUE] = 'Sim';
        $options[self::COND_FALSE] = 'Não';
        return $options;
    }

    protected function getCondJs($select)
    {
        $select->change("filterChangeBoolean($(this));");
        \App::addJs("$('#{$select->getId()}').change();");
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
        $conditionValue = $this->getConditionValue($index);

        $cond = NULL;
        if ($conditionValue == self::COND_TRUE)
        {
            $cond = new \Db\Where($columnName, '>=', 1, \Db\Cond::COND_AND, $this->getFilterType());
        }
        else if ($conditionValue == self::COND_FALSE)
        {
            $cond = new \Db\Where('(' . $columnName . ' = 0 OR ' . $columnName . ' IS NULL)', NULL, NULL, \Db\Cond::COND_AND, $this->getFilterType());
        }

        return $cond;
    }

}
