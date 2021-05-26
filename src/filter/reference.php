<?php

namespace Filter;

/**
 * Reference field filter
 */
class Reference extends \Filter\Collection
{

    const COND_TEXT = 'text';
    const COND_TEXT_EQUALS = 'textEquals';
    const TYPE_FIXED_VALUES = 1;
    const TYPE_SEARCH_VALUES = 2;

    /**
     *
     * @var \Db\Column\Column
     */
    protected $dbColumn;

    /**
     * Type of the combo
     * @var int
     */
    protected $type;

    public function __construct(\Component\Grid\Column $column = NULL, $filterType = NULL, $dbColumn = null)
    {
        parent::__construct($column, NULL, $filterType);

        $dom = \View\View::getDom();

        if ($dbColumn)
        {
            $this->dbColumn = $dbColumn;
        }
        //perhaps this if can be removed
        else if (method_exists($dom, 'getModel') && $column)
        {
            $model = $dom->getModel();
            $dbColumn = $model::getColumn($column->getName());
            $this->setDbColumn($dbColumn);
        }
    }

    protected function defineType()
    {
        $type = null;

        if (is_object($this->dbColumn) && $this->dbColumn->getClass())
        {
            //"temporary" workaround maroto
            if (stripos(mb_strtolower($this->dbColumn->getClass()), '\component\select\\') === 0)
            {
                $type = self::TYPE_FIXED_VALUES;
            }
            else
            {
                $type = self::TYPE_SEARCH_VALUES;
            }
        }
        else
        {
            $type = self::TYPE_FIXED_VALUES;
        }

        return $type;
    }

    public function getType()
    {
        if (!$this->type)
        {
            return $this->defineType();
        }

        return $this->type;
    }

    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    public function getDbColumn()
    {
        return $this->dbColumn;
    }

    public function setDbColumn($dbColumn)
    {
        $this->dbColumn = $dbColumn;
        return $this;
    }

    public function getConditionList()
    {
        $options = array();

        if ($this->getType() == self::TYPE_SEARCH_VALUES)
        {
            $options[self::COND_TEXT] = 'Texto';
            $options[self::COND_TEXT_EQUALS] = 'Texto - Igual';
            $options[self::COND_EQUALS] = 'Cód - Igual';
            $options[self::COND_NOT_EQUALS] = 'Cód - Diferente';
            $options[self::COND_NULL_OR_EMPTY] = 'Cód - Vazio';
            $options[self::COND_NOT_NULL_OR_EMPTY] = 'Cód - Não vazio';

            $this->setDefaultCondition(self::COND_TEXT);
        }
        else
        {
            $options[self::COND_EQUALS] = 'Igual';
            $options[self::COND_NOT_EQUALS] = 'Diferente';
            $options[self::COND_NULL_OR_EMPTY] = 'Vazio';
            $options[self::COND_NOT_NULL_OR_EMPTY] = 'Não vazio';

            $this->setDefaultCondition(self::COND_EQUALS);
        }

        return $options;
    }

    public function getInputValue($index = 0)
    {
        $columnValue = $this->getValueName();
        $class = 'filterInput reference';
        $value = $this->getFilterValue($index);
        $formatter = $this->column ? $this->column->getFormatter() : null;

        //add support for a formatter as \Db\ConstantValues
        if ($formatter instanceof \Db\ConstantValues)
        {
            $field = new \View\Select($this->getValueName() . '[]', $formatter->getArray(), $value, $class);
        }
        else if ($this->dbColumn->getReferenceField())
        {
            if ($this->getType() == self::TYPE_SEARCH_VALUES)
            {
                $field = new \View\Input($this->getValueName() . '[]', 'text', $value, 'filterInput');
            }
            else
            {
                $field = new \View\Ext\ReferenceField($this->dbColumn, $columnValue, $value, $class);
                $field->setName($field->getName() . '[]');
            }
        }
        else
        {
            $cValues = $this->dbColumn->getConstantValues();

            if ($cValues instanceof \Db\ConstantValues)
            {
                $cValues = $cValues->getArray();
            }

            $field = new \View\Select($this->getValueName() . '[]', $cValues, $value, $class);
        }

        $field->onPressEnter("$('#buscar').click()");

        return $field;
    }

    public function getFilterSql()
    {
        if (!$this->filterSql)
        {
            if ($this->dbColumn)
            {
                if ($this->dbColumn->getClass())
                {
                    $this->filterSql = $this->dbColumn->getReferenceSql(FALSE);
                }
                else
                {
                    $this->filterSql = $this->dbColumn->getName();
                }
            }
            else
            {
                $this->filterSql = $this->column ? $this->column->getSql() : $this->getFilterName();
            }
        }

        return $this->filterSql;
    }

    public function createWhere($index = 0)
    {
        $column = $this->getColumn();

        $dbColumn = $this->dbColumn;
        $columnName = $column ? $column->getSql() : $this->getFilterName();
        $filterName = $this->getValueName();
        $conditionValue = $this->getConditionValue($index);
        $filterValue = $this->getFilterValue($index);
        $wasFiltered = strlen($filterValue) > 0 || $filterValue == '0';
        $conditionType = $index > 0 ? \Db\Cond::COND_OR : \Db\Cond::COND_AND;
        $sql = $dbColumn && $dbColumn->getReferenceSql(FALSE) ? $dbColumn->getReferenceSql(FALSE) : $this->getFilterSql();

        if ($conditionValue && $wasFiltered && $sql)
        {
            if ($conditionValue == self::COND_TEXT)
            {
                return new \Db\Where($sql, 'like', \Db\Where::contains($filterValue), $conditionType);
            }
            else if ($conditionValue == self::COND_TEXT_EQUALS)
            {
                return new \Db\Where($sql, '=', $filterValue, $conditionType);
            }
            else
            {
                return parent::createWhere($index);
            }
        }
        else
        {
            return parent::createWhere($index);
        }

        return null;
    }

}
