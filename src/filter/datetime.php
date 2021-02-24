<?php

namespace Filter;

/**
 * Datetime filter
 */
class DateTime extends \Filter\Text
{

    const COND_IGUAL = '=';
    const COND_NOT_EQUALS = '!=';
    const COND_MAIOR = '>';
    const COND_MAIOR_IGUAL = '>=';
    const COND_MENOR = '<';
    const COND_MENOR_IGUAL = '<=';
    const COND_INTERVALO = 'between';
    const COND_TODAY = 'today';
    const COND_YESTERDAY = 'yesterday';
    const COND_TOMORROW = 'tomorrow';
    const COND_CURRENT_MONTH = 'currentmonth';
    const COND_PAST_MONTH = 'pastmonth';
    const COND_NEXT_MONTH = 'nextmonth';
    const COND_BIRTH_MONTH = 'birthmonth';

    public function __construct(\Component\Grid\Column $column = NULL, $filterName = \NULL, $filterType = NULL)
    {
        parent::__construct($column, $filterName, $filterType);
        $this->setDefaultCondition(self::COND_IGUAL);
    }

    public function getConditionList()
    {
        $options[''] = 'Não filtrar ...';
        $options[self::COND_TODAY] = 'Hoje';
        $options[self::COND_YESTERDAY] = 'Ontem';
        $options[self::COND_TOMORROW] = 'Amanhã';
        $options[self::COND_IGUAL] = 'Igual';
        $options[self::COND_NOT_EQUALS] = 'Diferente';

        $options[self::COND_MAIOR] = 'Maior';
        $options[self::COND_MAIOR_IGUAL] = 'Maior ou igual';
        $options[self::COND_MENOR] = 'Menor';
        $options[self::COND_MENOR_IGUAL] = 'Menor ou igual';
        $options[\Filter\Text::COND_NULL_OR_EMPTY] = 'Vazio';
        $options[\Filter\Text::COND_NOT_NULL_OR_EMPTY] = 'Não vazio';
        $options[self::COND_INTERVALO] = 'Intervalo';

        $options[self::COND_CURRENT_MONTH] = 'Mês (atual)';
        $options[self::COND_PAST_MONTH] = 'Mês (passado)';
        $options[self::COND_NEXT_MONTH] = 'Mês (próximo)';
        $options[self::COND_BIRTH_MONTH] = 'Mês de aniversário';

        return $options;
    }

    protected function getCondJs($select)
    {
        $select->change("filterChangeDate($(this));");
        \App::addJs("$('#{$select->getId()}').change();");
    }

    public function getInputValue($index = 0)
    {
        $columnValue = $this->getValueName();
        $view[0] = $input = new \View\Ext\DateInput($columnValue . '[]', $this->getFilterValue($index), 'filterInput filter-date-date');
        $view[0]->onPressEnter("$('#buscar').click()");
        $view[2] = $hide = new \View\Ext\DateInput($columnValue . 'Final[]', $this->getFilterValueFinal($index), 'filterInput filterDataFinal final');
        $view[2]->onPressEnter("$('#buscar').click()");
        $view[3] = $month = new \View\Select($columnValue . '[]', \Type\DateTime::listMonth(), $this->getFilterValue($index), 'filterInput filter-date-month');
        $month->onPressEnter("$('#buscar').click()")->setId($columnValue . '-month');

        $hide->addStyle('display', 'none');
        $month->addStyle('display', 'none');
        return $view;
    }

    public function createWhere($index = 0)
    {
        $column = $this->getColumn();
        $columnName = $this->getFilterSql();

        $conditionValue = $this->getConditionValue($index);
        $filterValue = $this->getFilterValue($index);
        $filterValueFinal = $this->getFilterValueFinal($index);
        $conditionType = $index > 0 ? \Db\Cond::COND_OR : \Db\Cond::COND_AND;

        //add support for clean value
        $filterValue = str_replace('__/__/____', '', $filterValue);
        $isFiltered = (strlen(trim($filterValue)) > 0);

        if ($conditionValue == self::COND_BIRTH_MONTH)
        {
            return new \Db\Where('MONTH(' . $columnName . ')', '=', $filterValue, $conditionType, $this->getFilterType());
        }
        else if ($conditionValue == self::COND_TODAY)
        {
            return new \Db\Where('DATE(' . $columnName . ')', '=', date('Y-m-d'), $conditionType, $this->getFilterType());
        }
        else if ($conditionValue == self::COND_YESTERDAY)
        {
            $date = \Type\Date::now()->addDay(-1);
            return new \Db\Where('DATE(' . $columnName . ')', '=', $date->toDb(), $conditionType, $this->getFilterType());
        }
        else if ($conditionValue == self::COND_TOMORROW)
        {
            $date = \Type\Date::now()->addDay(1);
            return new \Db\Where('DATE(' . $columnName . ')', '=', $date->toDb(), $conditionType, $this->getFilterType());
        }
        else if ($conditionValue == self::COND_CURRENT_MONTH)
        {
            $begin = \Type\Date::now()->setDay(1);
            $end = \Type\Date::now()->setLastDayOfMonth();
            return new \Db\Cond('DATE(' . $columnName . ') BETWEEN ? AND ? ', array($begin->toDb(), $end->toDb()), $conditionType, $this->getFilterType());
        }
        else if ($conditionValue == self::COND_PAST_MONTH)
        {
            $begin = \Type\Date::now()->addMonth(-1)->setDay(1);
            $end = \Type\Date::now()->addMonth(-1)->setLastDayOfMonth();
            return new \Db\Cond('DATE(' . $columnName . ') BETWEEN ? AND ? ', array($begin->toDb(), $end->toDb()), $conditionType, $this->getFilterType());
        }
        else if ($conditionValue == self::COND_NEXT_MONTH)
        {
            $begin = \Type\Date::now()->addMonth(1)->setDay(1);
            $end = \Type\Date::now()->addMonth(1)->setLastDayOfMonth();
            return new \Db\Cond('DATE(' . $columnName . ') BETWEEN ? AND ? ', array($begin->toDb(), $end->toDb()), $conditionType, $this->getFilterType());
        }
        else if ($conditionValue == self::COND_INTERVALO)
        {
            //&& $filterValueFinal != 0
            $date = new \Type\DateTime($filterValue);
            $dateFinal = new \Type\DateTime($filterValueFinal);
            //both dates filled
            if ($date->getDay() && $dateFinal->getDay())
            {
                $date->setTime(0, 0, 0);
                $dateFinal->setTime(23, 59, 59);

                $filterValueFinal = \Type\DateTime::get($filterValueFinal);
                return new \Db\Cond($columnName . ' ' . $conditionValue . ' ? AND ?', array($date->toDb(), $dateFinal->toDb()), $conditionType, $this->getFilterType());
            }
            else if ($date->getDay())
            {
                return new \Db\Where('DATE(' . $columnName . ')', '>=', $date->toDb());
            }
            else if ($dateFinal->getDay())
            {
                return new \Db\Where('DATE(' . $columnName . ')', '>=', $dateFinal->toDb());
            }
        }
        //this is equal, not equals, greather and etc
        else if ($conditionValue && $isFiltered)
        {
            $date = new \Type\DateTime($filterValue);

            //filter on by date, without time
            if ($date->getHour() == 0 && $date->getMinute() == 0 && $date->getSecond() == 0)
            {
                $date = new \Type\Date($filterValue);
                $columnName = 'DATE(' . $columnName . ')';
            }

            return new \Db\Where($columnName, $conditionValue, $date->toDb(), $conditionType, $this->getFilterType());
        }
        else if ($conditionValue == self::COND_NULL_OR_EMPTY)
        {
            //support null, empty string or zero date
            return new \Db\Cond('(' . $columnName . ' IS NULL OR ' . $columnName . ' = \'\' OR DATE(' . $columnName . ') = \'0000-00-00\')', NULL, $conditionType, $this->getFilterType());
        }
        else if ($conditionValue == self::COND_NOT_NULL_OR_EMPTY)
        {
            //support null, empty string or zero date
            return new \Db\Cond('(' . $columnName . ' IS NOT NULL AND ' . $columnName . ' != \'\' AND DATE(' . $columnName . ') != \'0000-00-00\')', NULL, $conditionType, $this->getFilterType());
        }
    }

}
