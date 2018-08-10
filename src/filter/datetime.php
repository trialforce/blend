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
    const COND_BIRTHDAY = 'birthday';

    public function getCondition()
    {
        $conditionName = $this->getConditionName();

        $options[self::COND_TODAY] = 'Hoje';
        $options[self::COND_YESTERDAY] = 'Ontem';
        $options[self::COND_TOMORROW] = 'Amanha';
        $options[self::COND_BIRTHDAY] = 'Aniversário';

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

        $options[self::COND_IGUAL] = 'Igual';
        $options[self::COND_INTERVALO] = 'Intervalo';
        $options[self::COND_MAIOR] = 'Maior';
        $options[self::COND_MAIOR_IGUAL] = 'Maior ou igual';
        $options[self::COND_MENOR] = 'Menor';
        $options[self::COND_MENOR_IGUAL] = 'Menor ou igual';
        $options[\Filter\Text::COND_NULL_OR_EMPTY] = 'Nulo ou vazio';
        $options[\Filter\Text::COND_EMPTY] = 'Vazio';
        $options[\Filter\Text::COND_NULL] = 'Nulo';

        $conditionValue = Request::get($conditionName);

        if (!$conditionValue)
        {
            $conditionValue = self::COND_TODAY;
        }

        $select = new \View\Select($conditionName, $options, $conditionValue, 'filterCondition span1_5 small');

        // js para esconder e mostrar campo
        $js = "if ( $(this).val() == 'between' ) { showEndDate(1, $(this).attr('id')); }
               else if ( $(this).val() == 'birthday' ) { showEndDate(2, $(this).attr('id')); }
               else { showEndDate(0, $(this).attr('id')); }";

        $select->change($js);
        $this->getCondJs($select);

        return $select;
    }

    public function getValue()
    {
        $columnValue = $this->getValueName();
        $view[] = $input = new \View\InputText($columnValue, Request::get($columnValue), 'small filterInput');
        $view[] = $label = new \View\Label($columnValue . 'LabelFinal', NULL, 'até', 'small filterInput');
        $view[] = $hide = new \View\InputText($columnValue . 'Final', Request::get($columnValue . 'Final'), 'small filterInput filterDataFinal');

        $hide->addStyle('display', 'none');
        $label->addStyle('display', 'none');

        return $view;
    }

    public function getDbCond()
    {
        $column = $this->getColumn();
        $columnName = $column->getName();
        $conditionName = $this->getConditionName();
        $filterName = $this->getValueName();

        $conditionValue = Request::get($conditionName);
        $filterValue = Request::get($filterName);
        $filterValueFinal = Request::get($filterName . 'Final');

        if (stripos($conditionValue, self::COND_MONTH_FIXED) === 0)
        {
            $explode = explode('-', $conditionValue);
            $month = isset($explode[1]) ? $explode[1] : 1;

            $begin = \Type\Date::now()->setMonth($month)->setDay(1);
            $end = \Type\Date::now()->setMonth($month)->setLastDayOfMonth();
            return new \Db\Cond('date(' . $columnName . ') BETWEEN ? AND ? ', array($begin->toDb(), $end->toDb()), \Db\Cond::COND_AND, $this->getFilterType());
        }
        if ($conditionValue == self::COND_TODAY)
        {
            return new \Db\Cond('date(' . $columnName . ') = ? ', date('Y-m-d'), \Db\Cond::COND_AND, $this->getFilterType());
        }
        else if ($conditionValue == self::COND_YESTERDAY)
        {
            $date = \Type\Date::now()->addDay(-1);
            return new \Db\Cond('date(' . $columnName . ') = ? ', $date->toDb(), \Db\Cond::COND_AND, $this->getFilterType());
        }
        else if ($conditionValue == self::COND_TOMORROW)
        {
            $date = \Type\Date::now()->addDay(1);
            return new \Db\Cond('date(' . $columnName . ') = ? ', $date->toDb(), \Db\Cond::COND_AND, $this->getFilterType());
        }
        else if ($conditionValue == self::COND_CURRENT_MONTH)
        {
            $begin = \Type\Date::now()->setDay(1);
            $end = \Type\Date::now()->setLastDayOfMonth();
            return new \Db\Cond('date(' . $columnName . ') BETWEEN ? AND ? ', array($begin->toDb(), $end->toDb()), \Db\Cond::COND_AND, $this->getFilterType());
        }
        else if ($conditionValue == self::COND_PAST_MONTH)
        {
            $begin = \Type\Date::now()->addMonth(-1)->setDay(1);
            $end = \Type\Date::now()->addMonth(-1)->setLastDayOfMonth();
            return new \Db\Cond('date(' . $columnName . ') BETWEEN ? AND ? ', array($begin->toDb(), $end->toDb()), \Db\Cond::COND_AND, $this->getFilterType());
        }
        else if ($conditionValue == self::COND_NEXT_MONTH)
        {
            $begin = \Type\Date::now()->addMonth(1)->setDay(1);
            $end = \Type\Date::now()->addMonth(1)->setLastDayOfMonth();
            return new \Db\Cond('date(' . $columnName . ') BETWEEN ? AND ? ', array($begin->toDb(), $end->toDb()), \Db\Cond::COND_AND, $this->getFilterType());
        }
        else if ($conditionValue == self::COND_INTERVALO && $filterValueFinal != 0)
        {
            $date = new \Type\DateTime($filterValue);
            $dateFinal = new \Type\DateTime($filterValueFinal);

            //filter on by date, without time
            if ($date->getHour() == 0 && $date->getMinute() == 0 && $date->getSecond() == 0 && $dateFinal->getHour() == 0 && $dateFinal->getMinute() == 0 && $dateFinal->getSecond() == 0)
            {
                $date = new \Type\Date($filterValue);
                $dateFinal = new \Type\Date($filterValueFinal);
                $columnName = 'date(' . $columnName . ')';
            }

            $filterValueFinal = \Type\DateTime::get($filterValueFinal);
            return new \Db\Cond($columnName . ' ' . $conditionValue . ' ? AND ?', array($date->toDb(), $dateFinal->toDb()), \Db\Cond::COND_AND, $this->getFilterType());
        }
        else if ($conditionValue == self::COND_BIRTHDAY && $filterValueFinal != 0)
        {
            $date = new \Type\DateTime($filterValue . '/1980');
            $dateFinal = new \Type\DateTime($filterValueFinal . '/1980');

            $mesA = $date->getMonth();
            $mesB = $dateFinal->getMonth();

            $diaA = $date->getDay();
            $diaB = $dateFinal->getDay();

            return new \Db\Cond('(month(' . $columnName . ') between ? and ?) and (day(' . $columnName . ') between ? and ?)', array($mesA, $mesB, $diaA, $diaB));
        }
        else if ($conditionValue && isset($filterValue) && $filterValue)
        {
            $date = new \Type\DateTime($filterValue);

            //filter on by date, without time
            if ($date->getHour() == 0 && $date->getMinute() == 0 && $date->getSecond() == 0)
            {
                $date = new \Type\Date($filterValue);
                $columnName = 'date(' . $columnName . ')';
            }

            return new \Db\Cond($columnName . ' ' . $conditionValue . ' ? ', $date->toDb(), \Db\Cond::COND_AND, $this->getFilterType());
        }

        return $this->getNullOrEmptyFilter($conditionValue, $columnName);
    }

}