<?php

namespace Filter;

/**
 * Reference field filter
 */
class Reference extends \Filter\Collection
{

    const COND_TEXT = 'text';

    /**
     *
     * @var \Db\Column
     */
    protected $dbColumn;

    public function __construct(\Component\Grid\Column $column, $filterType = NULL)
    {
        parent::__construct($column, NULL, $filterType);

        $dom = \View\View::getDom();

        if (method_exists($dom, 'getModel'))
        {
            $model = $dom->getModel();
            $dbColumn = $model::getColumn($column->getName());
            $this->setDbColumn($dbColumn);
        }
        else
        {
            throw new \Exception('Impossível encontrar modelo ao criar filtro de referencia');
        }

        if ($this->dbColumn->getClass())
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

        if ($this->dbColumn->getClass())
        {
            $options[self::COND_TEXT] = 'Texto';
            $options[self::COND_EQUALS] = 'Cód - Igual';
            $options[self::COND_NOT_EQUALS] = 'Cód - Diferente';
            $options[self::COND_NULL_OR_EMPTY] = 'Cód - Vazio';
            $options[self::COND_NOT_NULL_OR_EMPTY] = 'Cód - Não vazio';
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
        $formatter = $this->column->getFormatter();

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

    public function createWhere($index = 0)
    {
        $conditionValue = $this->getConditionValue($index);
        $filterValue = $this->getFilterValue($index);
        $wasFiltered = strlen($filterValue) > 0 || $filterValue == '0';
        $conditionType = $index > 0 ? \Db\Cond::COND_OR : \Db\Cond::COND_AND;

        if ($conditionValue && $conditionValue == self::COND_TEXT && $wasFiltered)
        {
            $dbColumn = $this->dbColumn;
            return new \Db\Where($dbColumn->getReferenceSql(FALSE), 'like', \Db\Where::contains($filterValue), $conditionType);
        }
        else
        {
            return parent::createWhere($index);
        }

        return null;
    }

}
