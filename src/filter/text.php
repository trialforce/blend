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
    const COND_EQUALS = '=';
    const COND_NOT_EQUALS = '!=';
    const COND_STARTSWITH = 'startsWith';
    const COND_ENDSWITH = 'endsWith';
    const COND_NULL = 'null';
    const COND_EMPTY = 'empty';
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

        //suport description filter
        if (strpos($this->getFilterName(), 'Description') > 0)
        {
            $label .=' (Texto)';
        }

        return $label;
    }

    public function getLabel()
    {
        return new \View\Label(NULL, $this->getValueName(), $this->getFilterLabel(), 'filterLabel');
    }

    public function getValue()
    {
        $columnValue = $this->getValueName();

        return new \View\Input($columnValue, \View\Input::TYPE_TEXT, Request::get($columnValue), 'small filterInput');
    }

    public function getCondition()
    {
        $conditionName = $this->getConditionName();

        $options[self::COND_LIKE] = 'Contém';
        $options[self::COND_EQUALS] = 'Igual';
        $options[self::COND_NOT_EQUALS] = 'Diferente';
        $options[self::COND_STARTSWITH] = 'Inicia com';
        $options[self::COND_ENDSWITH] = 'Termina com';
        $options[self::COND_NULL_OR_EMPTY] = 'Nulo ou vazio';
        $options[self::COND_EMPTY] = 'Vazio';
        $options[self::COND_NULL] = 'Nulo';

        $conditionValue = Request::get($conditionName);

        if (!$conditionValue)
        {
            $conditionValue = 'like';
        }

        $select = new \View\Select($conditionName, $options, $conditionValue, 'span1_5 small filterCondition');
        $this->getCondJs($select);

        return $select;
    }

    protected function getCondJs($select)
    {
        $valueName = $this->getValueName();
        $empty = self::COND_EMPTY;
        $null = self::COND_NULL;
        $nullOrEmpty = self::COND_NULL_OR_EMPTY;

        $select->change("if ( $(this).val() == '$empty' || $(this).val() == '$null' || $(this).val() == '$nullOrEmpty' || $(this).val() == 'today' ) { $('#$valueName').attr('readonly',true).val('').val(''); } else { $('#$valueName').attr('readonly',false); } ");
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
        $columnName = $column->getName();
        $conditionName = $this->getConditionName();
        $filterName = $this->getValueName();
        $conditionValue = Request::get($conditionName);
        $filterValue = trim(Request::get($filterName));

        //case of rereference description
        if (strpos($this->getFilterName(), 'Description') > 0)
        {
            $columnName = $this->getFilterName();
        }

        if ($conditionValue && (strlen(trim($filterValue)) > 0))
        {
            $filterValueExt = str_replace(' ', '%', $filterValue);

            if ($conditionValue == self::COND_EQUALS)
            {
                return new \Db\Cond('(' . $columnName . ' = ? )', $filterValue, \Db\Cond::COND_AND, $this->getFilterType());
            }
            else if ($conditionValue == self::COND_NOT_EQUALS)
            {
                return new \Db\Cond('(' . $columnName . ' != ? )', $filterValue, \Db\Cond::COND_AND, $this->getFilterType());
            }
            else if ($conditionValue == self::COND_LIKE)
            {
                return new \Db\Cond('(' . $columnName . ' like ? )', '%' . $filterValueExt . '%', \Db\Cond::COND_AND, $this->getFilterType());
            }
            else if ($conditionValue == self::COND_STARTSWITH)
            {
                return new \Db\Cond('(' . $columnName . ' like ? )', $filterValueExt . '%', \Db\Cond::COND_AND, $this->getFilterType());
            }
            else if ($conditionValue == self::COND_ENDSWITH)
            {
                return new \Db\Cond('(' . $columnName . ' like ? )', '%' . $filterValueExt, \Db\Cond::COND_AND, $this->getFilterType());
            }
        }

        return $this->getNullOrEmptyFilter($conditionValue, $columnName);
    }

    /**
     * Retorna o filtro para os casos de null or empty
     *
     * @param string $conditionValue
     * @param string $columnName
     * @return \Db\Cond
     */
    protected function getNullOrEmptyFilter($conditionValue, $columnName)
    {
        /* Valores que funcionam só com condição */
        if (in_array($conditionValue, array(self::COND_NULL, self::COND_EMPTY, self::COND_NULL_OR_EMPTY)))
        {
            if ($conditionValue == self::COND_NULL)
            {
                return new \Db\Cond('(' . $columnName . ' IS NULL )', NULL, \Db\Cond::COND_AND, $this->getFilterType());
            }
            else if ($conditionValue == self::COND_EMPTY)
            {
                return new \Db\Cond('(' . $columnName . ' = \'\' )', NULL, \Db\Cond::COND_AND, $this->getFilterType());
            }
            else if ($conditionValue == self::COND_NULL_OR_EMPTY)
            {
                return new \Db\Cond('(' . $columnName . ' IS NULL OR ' . $columnName . ' = \'\' )', NULL, \Db\Cond::COND_AND, $this->getFilterType());
            }
        }

        return NULL;
    }

}