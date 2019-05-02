<?php

namespace Filter;

/**
 * Date filter, with interval by default
 */
class DateInterval extends \Filter\DateTime
{

    public function __construct(\Component\Grid\Column $column, $filterName = \NULL, $filterType = NULL)
    {
        parent::__construct($column, $filterName, $filterType);
        $this->addDefaultValue(\Filter\DateTime::COND_INTERVALO, \Type\Date::now()->setDay(1), \Type\Date::now()->setLastDayOfMonth());
    }

}
