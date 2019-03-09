<?php

namespace Component\Grid;

/**
 * Um link qualquer que leve essa chave primária
 */
class ColorColumn extends \Component\Grid\Column
{

    public function __construct($name, $label = \NULL, $align = Column::ALIGN_LEFT, $dataType = \Db\Column::TYPE_VARCHAR)
    {
        parent::__construct($name, $label, $align, $dataType);
    }

    public function getValue($item, $line = NULL, \View\View $tr = NULL, \View\View $td = NULL)
    {
        $line = \NULL;
        $value = parent::getValue($item, $line, $tr, $td);

        $color = new \View\Div('color' . $line, NULL, 'colorColumn');
        $color->css('background-color', $value);

        $td->addClass('colorColumn');

        return $color;
    }

}
