<?php

namespace Filter;

use DataHandle\Request;

/**
 * Decimal filter
 */
class Decimal extends Integer
{

    public function getValue()
    {
        $columnValue = $this->getValueName();

        $input[0] = new \View\Ext\FloatInput($columnValue, Request::get($columnValue), NULL, NULL);
        $input[0]->addClass('filterInput');
        $input[0]->onPressEnter("$('#buscar').click()");

        $input[1] = new \View\Ext\FloatInput($columnValue . 'Final', Request::get($columnValue . 'Final'), NULL, NULL);
        $input[1]->addClass('filterInput final');
        $input[1]->onPressEnter("$('#buscar').click()");

        return $input;
    }

}
