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

    public function __construct(\Component\Grid\Column $column, $filterName = \NULL, $filterType = NULL)
    {
        parent::__construct($column, $filterName, $filterType);
        //$this->setDefaultCondition(self::COND_IGUAL);
    }

    protected function getCondJs($select)
    {
        $select->change("filterChangeInteger($(this));");
        \App::addJs("$('#{$select->getId()}').change();");
    }

    public function getConditionList()
    {
        $options = array();
        $options[self::COND_IGUAL] = 'Igual';
        $options[self::COND_MAIOR] = '*Maior';
        $options[self::COND_MAIOR_IGUAL] = '*Maior igual';
        $options[self::COND_MENOR] = '*Menor';
        $options[self::COND_MENOR_IGUAL] = '*Menor igual';
        $options[Text::COND_NOT_EQUALS] = '*Diferente';
        $options[self::COND_BETWEEN] = 'Intervalo';
        $options[\Filter\Text::COND_NULL_OR_EMPTY] = 'Vazio';
        $options[\Filter\Text::COND_NOT_NULL_OR_EMPTY] = 'Não vazio';

        return $options;
    }

    public function getInputValue($index = 0)
    {
        $columnValue = $this->getValueName();

        $input[0] = new \View\Ext\IntInput($columnValue . '[]', $this->getFilterValue($index), NULL, NULL, 'filterInput');
        $input[0]->onPressEnter("$('#buscar').click()");
        $input[1] = new \View\Ext\IntInput($columnValue . 'Final[]', $this->getFilterValueFinal($index), NULL, NULL, 'filterInput final');
        $input[1]->onPressEnter("$('#buscar').click()");

        return $input;
    }

    public function createWhere($index = 0)
    {
        $cond = null;
        $column = $this->getColumn();
        $columnName = $column->getSql();
        $conditionValue = $this->getConditionValue($index);
        $filterValue = $this->getFilterValue($index);
        $filterFinalValue = $this->getFilterValueFinal($index);
        $conditionType = $index > 0 ? \Db\Cond::COND_OR : \Db\Cond::COND_AND;
        $isFiltered = (strlen(trim($filterValue)) > 0);

        if ($conditionValue)
        {
            if ($conditionValue == self::COND_NULL_OR_EMPTY)
            {
                $cond = new \Db\Cond('(' . $columnName . ' IS NULL OR ' . $columnName . ' = \'\' OR ' . $columnName . ' = 0)', NULL, $conditionType, $this->getFilterType());
            }
            else if ($conditionValue == self::COND_NOT_NULL_OR_EMPTY)
            {
                $cond = new \Db\Cond('(' . $columnName . ' IS NOT NULL AND ' . $columnName . ' != \'\' AND ' . $columnName . ' != 0)', NULL, $conditionType, $this->getFilterType());
            }
            else if ($isFiltered)
            {
                if ($conditionValue == self::COND_BETWEEN)
                {
                    $values[] = \Type\Decimal::value($filterValue);
                    $values[] = \Type\Decimal::value($filterFinalValue);

                    $cond = new \Db\Cond($columnName . ' BETWEEN ? AND ?', $values, $conditionType, $this->getFilterType());
                }
                else
                {

                    $conditionType = \Db\Cond::COND_AND;
                    $filterValue = \Type\Decimal::value($filterValue);
                    $cond = new \Db\Where($columnName, $conditionValue, $filterValue);
                }
            }
        }

        return $cond;
    }

}
