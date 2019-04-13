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
        $this->setDefaultCondition(\Filter\DateTime::COND_INTERVALO);
        $this->setDefaultValue(\Type\Date::now()->setDay(1));
        $this->setDefaultValueFinal(\Type\Date::now()->setLastDayOfMonth());
    }

}
