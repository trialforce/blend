<?php

namespace Component\Grid;

/**
 * Smart date column
 * FIXME muito lenta
 */
class SmartDateColumn extends \Component\Grid\Column
{

    public function __construct($name = NULL, $label = NULL, $align = Column::ALIGN_LEFT)
    {
        parent::__construct($name, $label, $align);
        $this->setAlign(Column::ALIGN_RIGHT);
        $this->setType(\Db\Column::TYPE_DATETIME);
    }

    public function getValue($item, $line = NULL, \View\View $tr = NULL, \View\View $td = NULL)
    {
        //$this->makeEditable($item, $line, $tr, $td);
        $line = NULL;
        $value = \Component\Grid\Column::getColumnValue($this, $item, $line);

        //increase compatibility
        if (!$value instanceof \Type\DateTime)
        {
            $value = new \Type\DateTime($value);
        }

        $date = \Type\Date::get($value)->getSmartDate();
        $td->setTitle($value);

        return $date;
    }

}
