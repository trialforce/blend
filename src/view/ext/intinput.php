<?php

namespace View\Ext;

/**
 * Based on AutoNumeric http://www.decorplanit.com/plugin/
 * Limite max and min
 */
class IntInput extends \View\Input
{

    public function __construct($idName, $value = NULL, $vMax = 99999999999999, $vMin = -99999999999, $class = NULL)
    {
        parent::__construct($idName, \View\Input::TYPE_NUMBER, $value, $class);

        $vMax = $vMax ? $vMax : 99999999999999;
        $vMin = $vMin ? $vMin : -99999999999;

        $this->setAttribute('keyboard', 'numeric');

        $this->setAttribute('data-m-dec', 0);
        $this->setAttribute('data-a-sep', '');
        $this->setAttribute('data-a-dec', '.');
        $this->setAttribute('max', $vMax);
        $this->setAttribute('min', $vMin);

        $this->addClass('integer');
    }

    public function setValue($value)
    {
        if ($value instanceof \Type\Generic)
        {
            $value = $value->toDb();
        }

        //null or int value
        $value = $value === null ? null : intval($value);

        return parent::setValue($value);
    }

}
