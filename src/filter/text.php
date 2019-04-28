<?php

namespace Filter;

use DataHandle\Request;

/**
 * Default Text filter
 */
class Text
{

    /**
     * Grid column that this filter is relative to
     *
     * @var \Component\Grid\Column
     */
    protected $column;

    /**
     * filter name
     * @var string
     */
    protected $filterName = NULL;

    /**
     * Filter type
     * Consult FILTER_TYPE_ constants
     *
     * @var int
     */
    protected $filterType = '';

    /**
     * Default fitler type
     * @var string
     */
    protected $defaultValue;

    /**
     * Default value final Used for intervals
     * @var string
     */
    protected $defaultValueFinal;

    /**
     * Default filter condition
     *
     * @var string
     */
    protected $defaultCondition;

    //filter condition
    const COND_LIKE = 'like';
    const COND_NOT_LIKE = 'not like';
    const COND_EQUALS = '=';
    const COND_NOT_EQUALS = '!=';
    const COND_STARTSWITH = 'startsWith';
    const COND_ENDSWITH = 'endsWith';
    const COND_NULL_OR_EMPTY = 'nullorempty';
    //filter type
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

    /**
     * Return the relative collumn
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

    public function getDefaultValueFinal()
    {
        return $this->defaultValueFinal;
    }

    public function setDefaultValueFinal($defaultValueFinal)
    {
        $this->defaultValueFinal = $defaultValueFinal;
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

    /**
     * Create and return all the input of the filter
     * @return \VIew\Div
     */
    public function getInput()
    {
        $column = $this->column;

        $views[] = $inputLabel = $this->getInputLabel();

        //if is not fixed put the close button
        if ($this->getFilterType() && $this->getFilterType() . '' != '2')
        {
            $inputLabel->append(self::getCloseFilterButton());
        }

        $content = array();
        $content[] = $this->getInputCondition(0);
        $content[] = $this->getInputValue(0);

        $views[] = new \View\Div(null, $content, 'filterBase clearfix');
        $views[] = self::getAddFilterButton();

        $count = count($this->getFilterValues()) - 1;

        for ($index = 0; $index < $count; $index++)
        {
            $content = array();
            $content[] = $this->getInputCondition($index + 1);
            $content[] = $this->getInputValue($index + 1);
            $content[] = self::getRemoveFilterButton();
            $views[] = new \View\Div(null, $content, 'clearfix');
        }

        return new \VIew\Div($column->getName() . 'Filter', $views, 'filterField');
    }

    /**
     * Create the label that goes inside the input
     *
     * @return \View\Label
     */
    public function getInputLabel()
    {
        return new \View\Label(NULL, $this->getValueName() . '[]', $this->getFilterLabel(), 'filterLabel');
    }

    /**
     * Create and return the value part of input
     *
     * @return \View\Input
     */
    public function getInputValue($index = 0)
    {
        $input = new \View\Input($this->getValueName() . '[]', \View\Input::TYPE_TEXT, $this->getFilterValue($index), 'filterInput');
        $input->onPressEnter("$('#buscar').click()");

        return $input;
    }

    /**
     * Create Input condition
     *
     * @return \View\Select
     */
    public function getInputCondition($index = 0)
    {
        $select = new \View\Select($this->getConditionName() . '[]', $this->getConditionList(), $this->getConditionValue($index), 'filterCondition');
        $select->onPressEnter("$('#buscar').click()");
        $this->getCondJs($select);

        return $select;
    }

    /**
     * Return the condition list
     *
     * @return array
     */
    public function getConditionList()
    {
        $options = array();
        $options[self::COND_LIKE] = 'Contém';
        $options[self::COND_NOT_LIKE] = '*Não contém';
        $options[self::COND_EQUALS] = '*Igual';
        $options[self::COND_NOT_EQUALS] = '*Diferente';
        $options[self::COND_STARTSWITH] = 'Inicia com';
        $options[self::COND_ENDSWITH] = 'Termina com';
        $options[self::COND_NULL_OR_EMPTY] = 'Nulo ou vazio';

        return $options;
    }

    /**
     * Return the filer label/name
     *
     * @return string
     */
    public function getFilterLabel()
    {
        $column = $this->getColumn();
        $label = ucfirst($column->getLabel());

        return trim($label) == 'Cod' ? 'Código' : $label;
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
     * Return condition value, controls default value
     *
     * @return mixed condition value, controls default value
     */
    public function getConditionValues()
    {
        $conditionName = $this->getConditionName();
        return Request::get($conditionName);
    }

    /**
     * Return filter value, controls default value
     *
     * @return the filter value, controls default value
     */
    public function getFilterValues()
    {
        $filterName = $this->getValueName();
        return Request::get($filterName);
    }

    public function getConditionValue($index = 0)
    {
        $values = $this->getConditionValues();

        if (!isset($values[$index]))
        {
            $value = $this->getDefaultCondition();
        }
        else
        {
            $value = trim($values[$index]);
        }

        return $value;
    }

    public function getFilterValue($index = 0)
    {
        $values = $this->getFilterValues();

        if (!isset($values[$index]))
        {
            $value = $this->getDefaultValue();
        }
        else
        {
            $value = trim($values[$index]);
        }

        return $value;
    }

    /**
     * Return filter value, controls default value
     *
     * @return the filter value, controls default value
     */
    public function getFilterValuesFinal()
    {
        $filterName = $this->getValueName() . 'Final';

        return Request::get($filterName);
    }

    public function getFilterValueFinal($index = 0)
    {
        $values = $this->getFilterValuesFinal();

        if (!isset($values[$index]))
        {
            $value = $this->getDefaultValueFinal();
        }
        else
        {
            $value = trim($values[$index]);
        }

        return $value;
    }

    /**
     * Create the \Db\Where need to apply filter o \Db\Model or \Db\QueryBuilder
     *
     * @return \Db\Where
     */
    public function getDbCond()
    {
        $values = $this->getConditionValues();

        if (!is_array($values))
        {
            return null;
        }

        $wheres = array();
        $criteria = null;

        foreach ($values as $index => $value)
        {
            $where = $this->createWhere($index);

            if ($where)
            {
                $wheres[] = $where;
            }
        }

        if ($wheres)
        {
            $criteria = new \Db\Criteria($wheres);
        }

        return $criteria;
    }

    public function createWhere($index = 0)
    {
        $column = $this->getColumn();
        $columnSql = $column->getSql();
        $conditionValue = $this->getConditionValue($index);
        $filterValue = $this->getFilterValue($index);
        $conditionType = $index > 0 ? \Db\Cond::COND_OR : \Db\Cond::COND_AND;

        if ($conditionValue && $conditionValue == self::COND_NULL_OR_EMPTY)
        {
            $cond = new \Db\Where('( (' . $columnSql . ') IS NULL OR (' . $columnSql . ') = \'\' )', NULL, NULL, $conditionType, $this->getFilterType());
            return $cond;
        }
        else if ($conditionValue && (strlen(trim($filterValue)) > 0))
        {
            $filterValueExt = str_replace(' ', '%', $filterValue);

            if ($conditionValue == self::COND_EQUALS)
            {
                $conditionType = 'AND';
                return new \Db\Where('(' . $columnSql . ')', $conditionValue, $filterValue, $conditionType, $this->getFilterType());
            }
            else if ($conditionValue == self::COND_NOT_EQUALS)
            {
                $conditionType = 'AND';
                return new \Db\Where('(' . $columnSql . ')', $conditionValue, $filterValue, $conditionType, $this->getFilterType());
            }
            else if ($conditionValue == self::COND_LIKE)
            {
                return new \Db\Where('(' . $columnSql . ')', self::COND_LIKE, '%' . $filterValueExt . '%', $conditionType, $this->getFilterType());
            }
            else if ($conditionValue == self::COND_NOT_LIKE)
            {
                $conditionType = 'AND';
                return new \Db\Where('(' . $columnSql . ')', self::COND_NOT_LIKE, '%' . $filterValueExt . '%', $conditionType, $this->getFilterType());
            }
            else if ($conditionValue == self::COND_STARTSWITH)
            {
                return new \Db\Where('(' . $columnSql . ')', self::COND_LIKE, $filterValueExt . '%', $conditionType, $this->getFilterType());
            }
            else if ($conditionValue == self::COND_ENDSWITH)
            {
                return new \Db\Where('(' . $columnSql . ' )', self::COND_LIKE, '%' . $filterValueExt, $conditionType, $this->getFilterType());
            }
        }
    }

    /**
     * Return the close filter icon element
     *
     * @return \View\Ext\Icon
     */
    public static function getCloseFilterButton()
    {
        $icon = new \View\Ext\Icon('cancel');
        $icon->click('filterRemove(this)')->addClass('removeFilter');

        return $icon;
    }

    public static function getAddFilterButton()
    {
        $icon = new \View\Ext\Icon('plus');
        $icon->click('filterAdd(this)')->addClass('addFilter');

        return $icon;
    }

    public static function getRemoveFilterButton()
    {
        $icon = new \View\Ext\Icon('trash');
        $icon->click('filterTrash(this)')->addClass('trashFilter');

        return $icon;
    }

}
