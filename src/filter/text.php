<?php

namespace Filter;

use DataHandle\Request;

/**
 * Filtro de texto
 */
class Text
{

    /**
     * Coluna da grid
     *
     * @var \Component\Grid\Column
     */
    protected $column;
    protected $filterName = NULL;
    protected $filterType = '';

    const COND_LIKE = 'like';
    const COND_NOT_LIKE = 'not like';
    const COND_EQUALS = '=';
    const COND_NOT_EQUALS = '!=';
    const COND_STARTSWITH = 'startsWith';
    const COND_ENDSWITH = 'endsWith';
    const COND_NULL_OR_EMPTY = 'nullorempty';
    const FILTER_TYPE_DISABLE = 0;
    const FILTER_TYPE_ENABLE = 1;
    const FILTER_TYPE_ENABLE_SHOW_ALWAYS = 2;
    const FILTER_TYPE_ENABLE_HAVING = 'having';

    public function __construct(\Component\Grid\Column $column, $filterName = \NULL, $filterType = NULL)
    {
        $this->setColumn($column);
        $this->setFilterName($filterName);
        $this->setFilterType($filterType);
    }

    public function getFilterName()
    {
        if (is_null($this->filterName))
        {
            return $this->getColumn()->getSplitName();
        }

        return $this->filterName;
    }

    public function setFilterName($filterName)
    {
        $this->filterName = $filterName;
        return $this;
    }

    public function getFilterType()
    {
        return $this->filterType;
    }

    public function setFilterType($filterType)
    {
        $this->filterType = $filterType;
        return $this;
    }

    public function getInput()
    {
        $column = $this->column;

        $views[] = $this->getLabel();
        $views[] = $this->getCondition();
        $views[] = $this->getValue();

        return new \VIew\Div($column->getName() . 'Filter', $views, 'filterField');
    }

    /**
     *
     * @return \Component\Grid\Column
     */
    public function getColumn()
    {
        return $this->column;
    }

    public function setColumn($column)
    {
        $this->column = $column;

        return $this;
    }

    public function getFilterLabel()
    {
        $column = $this->getColumn();
        $label = $column->getLabel();

        return ucfirst($label);
    }

    public function getLabel()
    {
        return new \View\Label(NULL, $this->getValueName(), $this->getFilterLabel(), 'filterLabel');
    }

    public function getValue()
    {
        $columnValue = $this->getValueName();

        $input = new \View\Input($columnValue, \View\Input::TYPE_TEXT, Request::get($columnValue), 'filterInput');
        $input->onPressEnter("$('#buscar').click()");

        return $input;
    }

    public function getCondition()
    {
        $conditionName = $this->getConditionName();

        $options[self::COND_LIKE] = 'Contém';
        $options[self::COND_NOT_LIKE] = 'Não contém';
        $options[self::COND_EQUALS] = 'Igual';
        $options[self::COND_NOT_EQUALS] = 'Diferente';
        $options[self::COND_STARTSWITH] = 'Inicia com';
        $options[self::COND_ENDSWITH] = 'Termina com';
        $options[self::COND_NULL_OR_EMPTY] = 'Nulo ou vazio';

        $conditionValue = Request::get($conditionName);

        if (!$conditionValue)
        {
            $conditionValue = 'like';
        }

        $select = new \View\Select($conditionName, $options, $conditionValue, 'filterCondition');
        $select->onPressEnter("$('#buscar').click()");
        $this->getCondJs($select);

        return $select;
    }

    protected function getCondJs($select)
    {
        $select->change("filterChangeText($(this));");
        \App::addJs("$('#{$select->getId()}').change();");
    }

    public function getConditionName()
    {
        return $this->getFilterName() . 'Condition';
    }

    public function getValueName()
    {
        return $this->getFilterName() . 'Value';
    }

    public function getDbCond()
    {
        $column = $this->getColumn();
        $columnSql = $column->getSql();
        $conditionName = $this->getConditionName();
        $filterName = $this->getValueName();
        $conditionValue = Request::get($conditionName);
        $filterValue = trim(Request::get($filterName));

        if ($conditionValue && $conditionValue == self::COND_NULL_OR_EMPTY)
        {
            $cond = new \Db\Where('( (' . $columnSql . ') IS NULL OR (' . $columnSql . ') = \'\' )', NULL, NULL, \Db\Cond::COND_AND, $this->getFilterType());
            return $cond;
        }
        else if ($conditionValue && (strlen(trim($filterValue)) > 0))
        {
            $filterValueExt = str_replace(' ', '%', $filterValue);

            if ($conditionValue == self::COND_EQUALS || $conditionValue == self::COND_NOT_EQUALS)
            {
                return new \Db\Where('(' . $columnSql . ')', $conditionValue, $filterValue, \Db\Cond::COND_AND, $this->getFilterType());
            }
            else if ($conditionValue == self::COND_LIKE)
            {
                return new \Db\Where('(' . $columnSql . ')', self::COND_LIKE, '%' . $filterValueExt . '%', \Db\Cond::COND_AND, $this->getFilterType());
            }
            else if ($conditionValue == self::COND_NOT_LIKE)
            {
                return new \Db\Where('(' . $columnSql . ')', self::COND_NOT_LIKE, '%' . $filterValueExt . '%', \Db\Cond::COND_AND, $this->getFilterType());
            }
            else if ($conditionValue == self::COND_STARTSWITH)
            {
                return new \Db\Where('(' . $columnSql . ')', self::COND_LIKE, $filterValueExt . '%', \Db\Cond::COND_AND, $this->getFilterType());
            }
            else if ($conditionValue == self::COND_ENDSWITH)
            {
                return new \Db\Where('(' . $columnSql . ' )', self::COND_LIKE, '%' . $filterValueExt, \Db\Cond::COND_AND, $this->getFilterType());
            }
        }
    }

}
