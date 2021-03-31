<?php

namespace Filter;

/**
 * Reference field filter
 */
class Reference extends \Filter\Collection
{

    const COND_TEXT = 'text';
    const COND_TEXT_EQUALS = 'textEquals';

    /**
     *
     * @var \Db\Column\Column
     */
    protected $dbColumn;

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

        if (is_object($this->dbColumn) && $this->dbColumn->getClass())
        {
            $this->setDefaultCondition(self::COND_TEXT);
        }
        else
        {
            $this->setDefaultCondition(self::COND_EQUALS);
        }
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

        if ($this->dbColumn && $this->dbColumn->getClass())
        {
            $options[self::COND_TEXT] = 'Texto';
            $options[self::COND_TEXT_EQUALS] = 'Texto - Igual';
        }
        else
        {
            $options[self::COND_EQUALS] = 'Igual';
            $options[self::COND_NOT_EQUALS] = 'Diferente';
            $options[self::COND_NULL_OR_EMPTY] = 'Vazio';
            $options[self::COND_NOT_NULL_OR_EMPTY] = 'Não vazio';
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
            if ($this->dbColumn->getClass())
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
        $sql = $this->getFilterSql() ? $this->getFilterSql() : $dbColumn->getReferenceSql(FALSE);

        if ($conditionValue && $wasFiltered)
        {
            if ($sql && $conditionValue == self::COND_TEXT)
            {
                return new \Db\Where($sql, 'like', \Db\Where::contains($filterValue), $conditionType);
            }
            else if ($sql && $conditionValue == self::COND_TEXT_EQUALS)
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
