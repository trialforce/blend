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
    const COND_MONTH_FIXED = 'month-';

    public function __construct(\Component\Grid\Column $column, $filterName = \NULL, $filterType = NULL)
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
        $options[\Filter\Text::COND_NULL_OR_EMPTY] = 'Nulo ou vazio';
        $options[self::COND_INTERVALO] = 'Intervalo';

        $options[self::COND_CURRENT_MONTH] = 'Mês (atual)';
        $options[self::COND_PAST_MONTH] = 'Mês (passado)';
        $options[self::COND_NEXT_MONTH] = 'Mês (próximo)';

        $options[self::COND_MONTH_FIXED . '1'] = 'Janeiro';
        $options[self::COND_MONTH_FIXED . '2'] = 'Fevereiro';
        $options[self::COND_MONTH_FIXED . '3'] = 'Março';
        $options[self::COND_MONTH_FIXED . '4'] = 'Abril';
        $options[self::COND_MONTH_FIXED . '5'] = 'Maio';
        $options[self::COND_MONTH_FIXED . '6'] = 'Junho';
        $options[self::COND_MONTH_FIXED . '7'] = 'Julho';
        $options[self::COND_MONTH_FIXED . '8'] = 'Agosto';
        $options[self::COND_MONTH_FIXED . '9'] = 'Setembro';
        $options[self::COND_MONTH_FIXED . '10'] = 'Outubro';
        $options[self::COND_MONTH_FIXED . '11'] = 'Novembro';
        $options[self::COND_MONTH_FIXED . '12'] = 'Dezembro';

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
        $view[0] = $input = new \View\Ext\DateInput($columnValue . '[]', $this->getFilterValue($index), 'filterInput');
        $view[0]->onPressEnter("$('#buscar').click()");
        $view[2] = $hide = new \View\Ext\DateInput($columnValue . 'Final[]', $this->getFilterValueFinal($index), 'filterInput filterDataFinal final');
        $view[2]->onPressEnter("$('#buscar').click()");

        $hide->addStyle('display', 'none');
        return $view;
    }

    public function createWhere($index = 0)
    {
        $column = $this->getColumn();
        $columnName = $column->getName();

        $conditionValue = $this->getConditionValue($index);
        $filterValue = $this->getFilterValue($index);
        $filterValueFinal = $this->getFilterValueFinal($index);
        $conditionType = $index > 0 ? \Db\Cond::COND_OR : \Db\Cond::COND_AND;

        if (stripos($conditionValue, self::COND_MONTH_FIXED) === 0)
        {
            $explode = explode('-', $conditionValue);
            $month = isset($explode[1]) ? $explode[1] : 1;

            $begin = \Type\Date::now()->setMonth($month)->setDay(1);
            $end = \Type\Date::now()->setMonth($month)->setLastDayOfMonth();
            return new \Db\Cond('DATE(' . $columnName . ') BETWEEN ? AND ? ', array($begin->toDb(), $end->toDb()), $conditionType, $this->getFilterType());
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
                //filter on by date, without time
                if ($date->getHour() == 0 && $date->getMinute() == 0 && $date->getSecond() == 0 && $dateFinal->getHour() == 0 && $dateFinal->getMinute() == 0 && $dateFinal->getSecond() == 0)
                {
                    $date = new \Type\Date($filterValue);
                    $dateFinal = new \Type\Date($filterValueFinal);
                    $columnName = 'DATE(' . $columnName . ')';
                }

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
        else if ($conditionValue && isset($filterValue) && $filterValue)
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
            return new \Db\Cond('(' . $columnName . ' IS NULL OR ' . $columnName . ' = \'\' )', NULL, $conditionType, $this->getFilterType());
        }
    }

}
