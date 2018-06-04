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

        $options[self::COND_IGUAL] = 'Igual';
        $options[Text::COND_NOT_EQUALS] = 'Diferente';
        $options[self::COND_MAIOR] = 'Maior';
        $options[self::COND_MAIOR_IGUAL] = 'Maior ou igual';
        $options[self::COND_MENOR] = 'Menor';
        $options[self::COND_MENOR_IGUAL] = 'Menor ou igual';
        $options[self::COND_BETWEEN] = 'Intervalo';
        $options[\Filter\Text::COND_NULL_OR_EMPTY] = 'Nulo ou vazio';
        $options[\Filter\Text::COND_EMPTY] = 'Vazio';
        $options[\Filter\Text::COND_NULL] = 'Nulo';

        $conditionValue = Request::get($conditionName);

        if (!$conditionValue)
        {
            $conditionValue = self::COND_IGUAL;
        }

        $select = new \View\Select($conditionName, $options, $conditionValue, 'span1_5 small filterCondition');

        $js = "if ( $(this).val() == 'between') { $(this).parent().find('.final').show(); } else { $(this).parent().find('.final').hide(); }";
        $select->change($js);

        $this->getCondJs($select);

        return $select;
    }

    public function getValue()
    {
        $columnValue = $this->getValueName();

        $input[] = new \View\Ext\IntInput($columnValue, Request::get($columnValue), NULL, NULL, 'small filterInput');
        $input[] = new \View\Ext\IntInput($columnValue . 'Final', Request::get($columnValue . 'Final'), NULL, NULL, 'small filterInput final');

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

        if ($conditionValue && isset($filterValue) && \Type\Integer::isNumeric($filterValue))
        {
            if ($conditionValue == self::COND_BETWEEN)
            {
                $values[] = \Type\Decimal::value($filterValue);
                $values[] = \Type\Decimal::value($filterFinalValue);

                return new \Db\Cond($columnName . ' BETWEEN ? AND ?', $values, \Db\Cond::COND_AND, $this->getFilterType());
            }
            else
            {
                $filterValue = \Type\Decimal::value($filterValue);
                return new \Db\Cond($columnName . ' ' . $conditionValue . ' ? ', $filterValue, \Db\Cond::COND_AND, $this->getFilterType());
            }
        }

        return $this->getNullOrEmptyFilter($conditionValue, $columnName);
    }

}
