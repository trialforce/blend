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
     * Filter label
     * @var string
     */
    protected $filterLabel = null;

    /**
     * filter name
     * @var string
     */
    protected $filterName = NULL;

    /**
     * Filter sql
     * @var string
     */
    protected $filterSql = NULL;

    /**
     * Filter type/visibilty
     * Consult FILTER_TYPE_ constants
     *
     * @var int
     */
    protected $filterType = '';

    /**
     *
     * @var string
     */
    protected $filterGroup = '';

    /**
     * Default fitler type
     * @var string
     */
    protected $defaultValue;

    /**
     * Default value final Used for intervals
     * @var array
     */
    protected $defaultValueFinal;

    /**
     * Default filter condition
     *
     * @var array
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
    const COND_NOT_NULL_OR_EMPTY = 'notnullorempty';
    //filter type
    const FILTER_TYPE_DISABLE = 0;
    const FILTER_TYPE_ENABLE = 1;
    const FILTER_TYPE_ENABLE_SHOW_ALWAYS = 2;

    /**
     * Main group name
     */
    const GROUP_MAIN = 'AAAAAA';

    /**
     * Create a text filter
     *
     * @param \Component\Grid\Column $column the grid column
     * @param string $filterName the filter name
     * @param string $filterType the filter type
     */
    public function __construct($column = NULL, $filterName = NULL, $filterType = NULL)
    {
        if (is_string($column))
        {
            $filterName = $column;
            $column = null;
        }

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
        if (!$column)
        {
            return $this;
        }

        $this->column = $column;
        $this->setFilterName(\Db\Column\Column::getRealColumnName($this->getColumn()->getName()));

        return $this;
    }

    public function getFilterName()
    {
        if (is_null($this->filterName) && $this->getColumn())
        {
            $this->filterName = \Db\Column\Column::getRealColumnName($this->getColumn()->getName());
        }

        return $this->filterName;
    }

    public function setFilterName($filterName)
    {
        $this->filterName = $filterName;
        return $this;
    }

    public function getFilterSql()
    {
        if (!$this->filterSql)
        {
            $this->filterSql = $this->column ? $this->column->getSql() : $this->getFilterName();
        }

        return $this->filterSql;
    }

    public function setFilterSql($filterSql)
    {
        $this->filterSql = $filterSql;
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

    public function getFilterGroup()
    {
        return $this->filterGroup;
    }

    public function setFilterGroup($filterGroup)
    {
        $this->filterGroup = $filterGroup;
        return $this;
    }

    public function getDefaultValue()
    {
        return $this->defaultValue;
    }

    public function clearDefaultValue()
    {
        $this->defaultValue = null;
        $this->defaultValueFinal = null;
        $this->defaultCondition = null;

        return $this;
    }

    /**
     * Add a default value to filter
     *
     * @param string $defaultCondition
     * @param string $defaultValue
     * @param string $defaultValueFinal
     */
    public function addDefaultValue($defaultCondition, $defaultValue = NULL, $defaultValueFinal = NULL)
    {
        if (!$defaultCondition)
        {
            return $this;
        }

        $values = $this->getDefaultValue();
        $conditions = $this->getDefaultCondition();

        if (isCountable($values) && isCountable($conditions) && count($conditions) == 1 && count($values) == 0)
        {
            $this->clearDefaultValue();
        }

        $this->defaultCondition[] = $defaultCondition;

        if ($defaultValue || strlen($defaultValue) > 0)
        {
            $this->defaultValue[] = $defaultValue;
        }

        if ($defaultValueFinal)
        {
            $this->defaultValueFinal[] = $defaultValueFinal;
        }

        return $this;
    }

    public function setDefaultValue($defaultValue)
    {
        if ($defaultValue == null)
        {
            return $this;
        }

        //always convert to array
        if (!is_array($defaultValue))
        {
            $defaultValue = array($defaultValue);
        }

        $this->defaultValue = $defaultValue;

        return $this;
    }

    public function getDefaultValueFinal()
    {
        return $this->defaultValueFinal;
    }

    public function setDefaultValueFinal($defaultValueFinal)
    {
        if ($defaultValueFinal == null)
        {
            return $this;
        }

        if (!is_array($defaultValueFinal))
        {
            $defaultValueFinal = array($defaultValueFinal);
        }

        $this->defaultValueFinal = $defaultValueFinal;

        return $this;
    }

    public function getDefaultCondition()
    {
        return $this->defaultCondition;
    }

    public function setDefaultCondition($defaultCondition)
    {
        if ($defaultCondition == null)
        {
            return $this;
        }

        if (!is_array($defaultCondition))
        {
            $defaultCondition = array($defaultCondition);
        }

        $this->defaultCondition = $defaultCondition;
        return $this;
    }

    /**
     * Create and return all the input of the filter
     * @return \View\Div
     * @throws \Exception
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

        //without post, use default values
        if ($count == -1)
        {
            $count = count($this->getDefaultValue()) - 1;
        }

        for ($index = 0; $index < $count; $index++)
        {
            $content = array();
            $content[] = $this->getInputCondition($index + 1);
            $content[] = $this->getInputValue($index + 1);
            $content[] = self::getRemoveFilterButton();
            $views[] = new \View\Div(null, $content, 'clearfix filterBase-cloned');
        }

        $columnName = $column ? $column->getName() : $this->getFilterName();

        return new \View\Div($columnName . 'Filter', $views, 'filterField');
    }

    /**
     * Create the label that goes inside the input
     *
     * @return \View\Label
     * @throws \Exception
     */
    public function getInputLabel()
    {
        $label = $this->getFilterLabel();

        if ($this->getFilterGroup() && $this->getFilterGroup() != \Filter\Text::GROUP_MAIN)
        {
            $label = $this->getFilterGroup() . ' - ' . $label;
        }

        return new \View\Label(NULL, $this->getValueName() . '[]', $label, 'filterLabel');
    }

    /**
     * Create and return the value part of input
     *
     * @return \View\Input
     * @throws \Exception
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
     * @throws \Exception
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
        $options[self::COND_NULL_OR_EMPTY] = 'Vazio';
        $options[self::COND_NOT_NULL_OR_EMPTY] = 'Não Vazio';

        return $options;
    }

    public function setFilterLabel($filterLabel)
    {
        $this->filterLabel = trim($filterLabel);
        return $this;
    }

    /**
     * Return the filer label/name
     *
     * @return string
     */
    public function getFilterLabel()
    {
        if (!$this->filterLabel)
        {
            $this->filterLabel = trim(ucfirst($this->getFilterName()));
        }

        return $this->filterLabel;
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
        $values = Request::get($conditionName);

        //add support for simples parameters
        if (is_string($values))
        {
            $values = array($values);
        }

        return $values;
    }

    /**
     * Return filter value, controls default value
     *
     * @return string filter value, controls default value
     */
    public function getFilterValues()
    {
        $filterName = $this->getValueName();
        $values = Request::get($filterName);

        //add support for simples parameters
        if (is_string($values) || is_null($values))
        {
            $values = array($values);
        }

        return $values;
    }

    public function getConditionValue($index = 0)
    {
        $values = $this->getConditionValues();

        if (!isset($values[$index]))
        {
            $defaultValue = $this->getDefaultCondition();
            $value = isset($defaultValue[$index]) ? $defaultValue[$index] : null;
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
            $defaultValue = $this->getDefaultValue();
            $value = isset($defaultValue[$index]) ? $defaultValue[$index] : null;
        }
        else
        {
            $value = trim($values[$index]);
        }

        return $value;
    }

    /**
     * Return filter final value, controls default value
     *
     * @return string filter final value, controls default value
     */
    public function getFilterValuesFinal()
    {
        $filterName = $this->getValueName() . 'Final';
        $values = Request::get($filterName);

        //add support for simples parameters
        if (is_string($values))
        {
            $values = array($values);
        }

        return $values;
    }

    public function getFilterValueFinal($index = 0)
    {
        $values = $this->getFilterValuesFinal();

        if (!isset($values[$index]))
        {
            $defaultValue = $this->getDefaultValueFinal();
            $value = isset($defaultValue[$index]) ? $defaultValue[$index] : null;
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
     * @return \Db\Filter
     */
    public function getDbCond()
    {
        $values = $this->getConditionValues();

        if (!is_array($values))
        {
            $values = $this->defaultCondition;
        }

        //without post, with default values
        if (!is_array($values))
        {
            return $this->createWhere(0);
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
            //optimize to avoid need of \Db\Criteria
            if (count($wheres) == 1)
            {
                return $wheres[0];
            }
            else
            {
                $criteria = new \Db\Criteria($wheres);
            }
        }

        return $criteria;
    }

    public function createWhere($index = 0)
    {
        $cond = null;
        $column = $this->getColumn();
        $columnSql = $this->getFilterSql();
        $conditionValue = $this->getConditionValue($index);
        $filterValue = $this->getFilterValue($index);
        $conditionType = $index > 0 ? \Db\Cond::COND_OR : \Db\Cond::COND_AND;
        $isFiltered = (strlen(trim($filterValue.'')) > 0);

        if ($conditionValue)
        {
            if ($conditionValue == self::COND_NULL_OR_EMPTY)
            {
                $cond = new \Db\Where('( (' . $columnSql . ') IS NULL OR (' . $columnSql . ') = \'\' )', NULL, NULL, $conditionType);
            }
            else if ($conditionValue == self::COND_NOT_NULL_OR_EMPTY)
            {
                $cond = new \Db\Where('( (' . $columnSql . ') IS NOT NULL AND (' . $columnSql . ') != \'\' )', NULL, NULL, $conditionType);
            }
            else if ($isFiltered)
            {
                $filterValueExt = str_replace(' ', '%', $filterValue);

                if ($conditionValue == self::COND_EQUALS)
                {
                    $cond = new \Db\Where('(' . $columnSql . ')', $conditionValue, $filterValue, $conditionType);
                }
                else if ($conditionValue == self::COND_NOT_EQUALS)
                {
                    $conditionType = 'AND';
                    $cond = new \Db\Where('(' . $columnSql . ')', $conditionValue, $filterValue, $conditionType);
                }
                else if ($conditionValue == self::COND_LIKE)
                {
                    $cond = new \Db\Where('(' . $columnSql . ')', self::COND_LIKE, '%' . $filterValueExt . '%', $conditionType);
                }
                else if ($conditionValue == self::COND_NOT_LIKE)
                {
                    $conditionType = 'AND';
                    $cond = new \Db\Where('(' . $columnSql . ')', self::COND_NOT_LIKE, '%' . $filterValueExt . '%', $conditionType);
                }
                else if ($conditionValue == self::COND_STARTSWITH)
                {
                    $cond = new \Db\Where('(' . $columnSql . ')', self::COND_LIKE, $filterValueExt . '%', $conditionType);
                }
                else if ($conditionValue == self::COND_ENDSWITH)
                {
                    $cond = new \Db\Where('(' . $columnSql . ' )', self::COND_LIKE, '%' . $filterValueExt, $conditionType);
                }
            }
        }

        return $cond;
    }

    /**
     * Return the close filter icon element
     *
     * @return \View\Ext\Icon
     */
    public static function getCloseFilterButton()
    {
        $icon = new \View\Ext\Icon('trash');
        $icon->click('filterRemove(this)')->addClass('removeFilter');

        return $icon;
    }

    public static function getAddFilterButton()
    {
        $icon = new \View\Ext\Icon('plus');
        $icon->click('filterAdd(this)')->addClass('addFilter');
        $icon->setTitle('Adicionar filtro');

        return $icon;
    }

    public static function getRemoveFilterButton()
    {
        $icon = new \View\Ext\Icon('trash');
        $icon->click('filterTrash(this)')->addClass('trashFilter');
        $icon->setTitle('Remover filtro');

        return $icon;
    }

}
