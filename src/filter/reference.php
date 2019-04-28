<?php

namespace Filter;

use DataHandle\Request;

/**
 * Reference field filter
 *
 */
class Reference extends \Filter\Collection
{

    const COND_TEXT = 'text';

    /**
     *
     * @var \Db\Column
     */
    protected $dbColumn;

    public function __construct(\Component\Grid\Column $column, \Db\Column $dbColumn = NULL, $filterType = NULL)
    {
        parent::__construct($column, NULL, $filterType);
        $this->setDbColumn($dbColumn);

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
            $options[self::COND_NULL_OR_EMPTY] = 'Cód - Nulo ou vazio';
        }
        else
        {
            $options[self::COND_EQUALS] = 'Igual';
            $options[self::COND_NOT_EQUALS] = 'Diferente';
            $options[self::COND_NULL_OR_EMPTY] = 'Nulo ou vazio';
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

        //$field->setMultiple(true);
        $field->onPressEnter("$('#buscar').click()");

        return $field;
    }

    /* public function getCondition()
      {
      $conditionName = $this->getConditionName();

      $select = new \View\Select($conditionName . '[]',$this->getConditionList(), $this->ge, 'filterCondition');
      $this->getCondJs($select);

      return $select;
      } */

    public function createWhere($index = 0)
    {
        $conditionValue = $this->getConditionValue($index);
        $filterValue = $this->getFilterValue($index);

        if ($conditionValue && ($filterValue || $filterValue == 0) && $conditionValue == self::COND_TEXT)
        {
            $dbColumn = $this->dbColumn;
            return new \Db\Where($dbColumn->getReferenceSql(FALSE), 'like', \Db\Where::contains($filterValue));
        }
        else
        {
            return parent::createWhere($index);
        }

        return null;
    }

}
