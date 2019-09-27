<?php

namespace Filter;

/**
 * Decimal filter
 */
class Decimal extends Integer
{

    public function getInputValue($index = 0)
    {
        $columnValue = $this->getValueName();

        $input[0] = new \View\Ext\FloatInput($columnValue . '[]', $this->getFilterValue($index), NULL, NULL);
        $input[0]->addClass('filterInput')->onPressEnter("$('#buscar').click()");

        $input[1] = new \View\Ext\FloatInput($columnValue . 'Final[]', $this->getFilterValueFinal($index), NULL, NULL);
        $input[1]->addClass('filterInput final')->onPressEnter("$('#buscar').click()");
        return $input;
    }

}
