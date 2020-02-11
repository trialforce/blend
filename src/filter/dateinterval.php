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
        $this->clearDefaultValue();
        $start = \Type\Date::now()->setDay(1);
        $end = \Type\Date::now()->setLastDayOfMonth();
        $this->addDefaultValue(\Filter\DateTime::COND_INTERVALO, $start, $end);
    }

}
