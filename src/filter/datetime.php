<?php

namespace Filter;

use DataHandle\Request;

/**
 * Description of int
 *
 * @author eduardo
 */
class DateTime extends \Filter\Text
{

    const COND_IGUAL = '=';
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

    protected $defaultValueFinal;

    public function __construct(\Component\Grid\Column $column, $filterName = \NULL, $filterType = NULL)
    {
        parent::__construct($column, $filterName, $filterType);
        $this->setDefaultCondition(self::COND_IGUAL);
    }

    public function getDefaultValueFinal()
    {
        return $this->defaultValueFinal;
    }

    public function setDefaultValueFinal($defaultValueFinal)
    {
        $this->defaultValueFinal = $defaultValueFinal;
        return $this;
    }

    /**
     * Return filter value, controls default value
     *
     * @return the filter value, controls default value
     */
    public function getFilterValueFinal()
    {
        $filterName = $this->getValueName() . 'Final';
        $filterValue = trim(Request::get($filterName));

        if (!isset($_REQUEST[$filterName]))
        {
            $filterValue = $this->getDefaultValueFinal();
        }

        return $filterValue;
    }

    public function getCondition()
    {
        $conditionName = $this->getConditionName();

        $options[''] = 'Não filtrar ...';
        $options[self::COND_TODAY] = 'Hoje';
        $options[self::COND_YESTERDAY] = 'Ontem';
        $options[self::COND_TOMORROW] = 'Amanha';
        $options[self::COND_IGUAL] = '=';

        $options[self::COND_MAIOR] = '>';
        $options[self::COND_MAIOR_IGUAL] = '>=';
        $options[self::COND_MENOR] = '<';
        $options[self::COND_MENOR_IGUAL] = '<=';
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

        $select = new \View\Select($conditionName, $options, $this->getConditionValue(), 'filterCondition');
        $select->onPressEnter("$('#buscar').click()");

        // js para esconder e mostrar campo
        $select->change('filterChangeDate($(this));');

        return $select;
    }

    public function getValue()
    {
        $columnValue = $this->getValueName();
        $view[0] = $input = new \View\Ext\DateInput($columnValue, $this->getFilterValue(), 'filterInput');
        $view[0]->onPressEnter("$('#buscar').click()");
        $view[2] = $hide = new \View\Ext\DateInput($columnValue . 'Final', $this->getFilterValueFinal(), 'filterInput filterDataFinal');
        $view[2]->onPressEnter("$('#buscar').click()");

        $hide->addStyle('display', 'none');
        return $view;
    }

    public function getDbCond()
    {
        $column = $this->getColumn();
        $columnName = $column->getName();

        $conditionValue = $this->getConditionValue();
        $filterValue = $this->getFilterValue();

        $filterName = $this->getValueName();
        $filterValueFinal = $this->getFilterValueFinal();

        if (stripos($conditionValue, self::COND_MONTH_FIXED) === 0)
        {
            $explode = explode('-', $conditionValue);
            $month = isset($explode[1]) ? $explode[1] : 1;

            $begin = \Type\Date::now()->setMonth($month)->setDay(1);
            $end = \Type\Date::now()->setMonth($month)->setLastDayOfMonth();
            return new \Db\Cond('DATE(' . $columnName . ') BETWEEN ? AND ? ', array($begin->toDb(), $end->toDb()), \Db\Cond::COND_AND, $this->getFilterType());
        }
        else if ($conditionValue == self::COND_TODAY)
        {
            return new \Db\Where('DATE(' . $columnName . ')', '=', date('Y-m-d'), \Db\Cond::COND_AND, $this->getFilterType());
        }
        else if ($conditionValue == self::COND_YESTERDAY)
        {

            $date = \Type\Date::now()->addDay(-1);
            return new \Db\Where('DATE(' . $columnName . ')', '=', $date->toDb(), \Db\Cond::COND_AND, $this->getFilterType());
        }
        else if ($conditionValue == self::COND_TOMORROW)
        {
            $date = \Type\Date::now()->addDay(1);
            return new \Db\Where('DATE(' . $columnName . ')', '=', $date->toDb(), \Db\Cond::COND_AND, $this->getFilterType());
        }
        else if ($conditionValue == self::COND_CURRENT_MONTH)
        {
            $begin = \Type\Date::now()->setDay(1);
            $end = \Type\Date::now()->setLastDayOfMonth();
            return new \Db\Cond('DATE(' . $columnName . ') BETWEEN ? AND ? ', array($begin->toDb(), $end->toDb()), \Db\Cond::COND_AND, $this->getFilterType());
        }
        else if ($conditionValue == self::COND_PAST_MONTH)
        {
            $begin = \Type\Date::now()->addMonth(-1)->setDay(1);
            $end = \Type\Date::now()->addMonth(-1)->setLastDayOfMonth();
            return new \Db\Cond('DATE(' . $columnName . ') BETWEEN ? AND ? ', array($begin->toDb(), $end->toDb()), \Db\Cond::COND_AND, $this->getFilterType());
        }
        else if ($conditionValue == self::COND_NEXT_MONTH)
        {
            $begin = \Type\Date::now()->addMonth(1)->setDay(1);
            $end = \Type\Date::now()->addMonth(1)->setLastDayOfMonth();
            return new \Db\Cond('DATE(' . $columnName . ') BETWEEN ? AND ? ', array($begin->toDb(), $end->toDb()), \Db\Cond::COND_AND, $this->getFilterType());
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
                return new \Db\Cond($columnName . ' ' . $conditionValue . ' ? AND ?', array($date->toDb(), $dateFinal->toDb()), \Db\Cond::COND_AND, $this->getFilterType());
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
        //TODO what is this situation?????
        else if ($conditionValue && isset($filterValue) && $filterValue)
        {
            $date = new \Type\DateTime($filterValue);

            //filter on by date, without time
            if ($date->getHour() == 0 && $date->getMinute() == 0 && $date->getSecond() == 0)
            {
                $date = new \Type\Date($filterValue);
                $columnName = 'DATE(' . $columnName . ')';
            }

            return new \Db\Where($columnName . ' ', $conditionValue, $date->toDb(), \Db\Cond::COND_AND, $this->getFilterType());
        }
        else if ($conditionValue == self::COND_NULL_OR_EMPTY)
        {
            return new \Db\Cond('(' . $columnName . ' IS NULL OR ' . $columnName . ' = \'\' )', NULL, \Db\Cond::COND_AND, $this->getFilterType());
        }
    }

}
