<?php

namespace View\Ext;

/**
 * A Simple html color input
 */
class ColorInput extends \View\Input
{
    public function __construct($id = \NULL, $value = \NULL, $class = \NULL)
    {
        if ($value instanceof \Media\Color)
        {
            $value = $value->getHex();
        }

        parent::__construct($id, \View\Input::TYPE_COLOR, $value, $class);
    }

    public function setValue($value)
    {
        if ($value instanceof \Media\Color)
        {
            $value = $value->getHex();
        }

        parent::setValue($value);
    }
}