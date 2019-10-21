<?php

namespace Component\Grid;

/**
 *
 */
class PercentColumn extends \Component\Grid\Column
{

    public function __construct($name, $label = NULL, $align = Column::ALIGN_CENTER, $dataType = \Db\Column::TYPE_INTEGER)
    {
        parent::__construct($name, $label, $align, $dataType);
    }

    public function getValue($item, $line = NULL, \View\View $tr = NULL, \View\View $td = NULL)
    {
        $value = \Type\Integer::get(\DataSource\Grab::getUserValue($this, $item, $line));

        return self::getPercentBar($value);
    }

    /**
     * Return a simples percent bar
     *
     * @param int $value
     */
    public static function getPercentBar($value, $class = NULL)
    {
        $divInn = new \View\Div(NULL, $value . '%');
        $divInn->addStyle('width', $value . '%');

        $divOut = new \View\Div(null, $divInn, 'percentColumn');
        $divOut->setTitle($value . '%');

        if ($class)
        {
            $divOut->addClass($class);
        }

        return $divOut;
    }

}