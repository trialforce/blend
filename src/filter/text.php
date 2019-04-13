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
    protected $defaultValue;
    protected $defaultCondition;

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
        $this->setDefaultCondition('like');
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

    public function getDefaultValue()
    {
        return $this->defaultValue;
    }

    public function setDefaultValue($defaultValue)
    {
        $this->defaultValue = $defaultValue;
        return $this;
    }

    public function getDefaultCondition()
    {
        return $this->defaultCondition;
    }

    public function setDefaultCondition($defaultCondition)
    {
        $this->defaultCondition = $defaultCondition;
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

        $input = new \View\Input($columnValue, \View\Input::TYPE_TEXT, $this->getFilterValue(), 'filterInput');
        $input->onPressEnter("$('#buscar').click()");

        return $input;
    }

    /**
     * Return condition value, controls default value
     *
     * @return mixed condition value, controls default value
     */
    public function getConditionValue()
    {
        $conditionName = $this->getConditionName();
        $conditionValue = Request::get($conditionName);

        //get from default condition, if not posted
        if (!isset($_REQUEST[$conditionName]))
        {
            $conditionValue = $this->getDefaultCondition();
        }

        return $conditionValue;
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

        $conditionValue = $this->getConditionValue();

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

    /**
     * Return filter value, controls default value
     *
     * @return the filter value, controls default value
     */
    public function getFilterValue()
    {
        $filterName = $this->getValueName();
        $filterValue = trim(Request::get($filterName));

        if (!isset($_REQUEST[$filterName]))
        {
            $filterValue = $this->getDefaultValue();
        }

        return $filterValue;
    }

    public function getDbCond()
    {
        $column = $this->getColumn();
        $columnSql = $column->getSql();
        $conditionValue = $this->getConditionValue();
        $filterValue = $this->getFilterValue();

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
