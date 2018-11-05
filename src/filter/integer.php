<?php

namespace Filter;

use DataHandle\Request;

/**
 * Description of int
 *
 * @author eduardo
 */
class Integer extends \Filter\Text
{

    const COND_IGUAL = '=';
    const COND_MAIOR = '>';
    const COND_MAIOR_IGUAL = '>=';
    const COND_MENOR = '<';
    const COND_MENOR_IGUAL = '<=';
    const COND_BETWEEN = 'between';

    public function getCondition()
    {
        $conditionName = $this->getConditionName();

        $options[self::COND_IGUAL] = '=';
        $options[Text::COND_NOT_EQUALS] = '<>';
        $options[self::COND_MAIOR] = '>';
        $options[self::COND_MAIOR_IGUAL] = '>=';
        $options[self::COND_MENOR] = '<';
        $options[self::COND_MENOR_IGUAL] = '<=';
        $options[self::COND_BETWEEN] = 'Intervalo';
        $options[\Filter\Text::COND_NULL_OR_EMPTY] = 'Nulo ou vazio';

        $conditionValue = Request::get($conditionName);

        if (!$conditionValue)
        {
            $conditionValue = self::COND_IGUAL;
        }

        $select = new \View\Select($conditionName, $options, $conditionValue, 'filterCondition');
        $select->onPressEnter("$('#buscar').click()");

        $select->change('filterChangeInteger($(this));');

        return $select;
    }

    public function getValue()
    {
        $columnValue = $this->getValueName();

        $input[0] = new \View\Ext\IntInput($columnValue, Request::get($columnValue), NULL, NULL, 'filterInput');
        $input[0]->onPressEnter("$('#buscar').click()");
        $input[1] = new \View\Ext\IntInput($columnValue . 'Final', Request::get($columnValue . 'Final'), NULL, NULL, 'filterInput final');
        $input[1]->onPressEnter("$('#buscar').click()");

        return $input;
    }

    public function getFilterLabel()
    {
        $label = parent::getFilterLabel();

        return trim($label) == 'Cod' ? 'Código' : $label;
    }

    public function getDbCond()
    {
        $column = $this->getColumn();
        $columnName = $column->getName();
        $conditionName = $this->getConditionName();
        $filterName = $this->getValueName();
        $conditionValue = Request::get($conditionName);
        $filterValue = Request::get($filterName);
        $filterFinalValue = Request::get($filterName . 'Final');

        $cond = null;

        if ($conditionValue && isset($filterValue))
        {
            if ($conditionValue == self::COND_BETWEEN)
            {
                $values[] = \Type\Decimal::value($filterValue);
                $values[] = \Type\Decimal::value($filterFinalValue);

                $cond = new \Db\Cond($columnName . ' BETWEEN ? AND ?', $values, \Db\Cond::COND_AND, $this->getFilterType());
            }
            else if ($conditionValue == self::COND_NULL_OR_EMPTY)
            {
                $cond = new \Db\Cond('(' . $columnName . ' IS NULL OR ' . $columnName . ' = \'\' )', NULL, \Db\Cond::COND_AND, $this->getFilterType());
            }
            else
            {
                $filterValue = \Type\Decimal::value($filterValue);
                $cond = new \Db\Cond($columnName . ' ' . $conditionValue . ' ? ', $filterValue, \Db\Cond::COND_AND, $this->getFilterType());
            }
        }

        return $cond;
    }

}
