<?php

namespace Filter;

/**
 * Reference field filter
 */
class FatherSearch extends \Filter\Collection
{

    const COND_TEXT = 'text';

    /**
     *
     * @var \Db\Column\Column
     */
    protected $dbColumn;

    public function __construct(\Component\Grid\Column $column, $filterType = NULL, $dbColumn = null)
    {
        parent::__construct($column, NULL, $filterType);

        $dom = \View\View::getDom();

        if ($dbColumn)
        {
            $this->dbColumn = $dbColumn;
        }
        //perhaps this if can be removed
        else if (method_exists($dom, 'getModel'))
        {
            $model = $dom->getModel();
            $dbColumn = $model::getColumn($column->getName());
            $this->setDbColumn($dbColumn);
        }
        else
        {
            throw new \Exception('Impossível encontrar modelo ao criar filtro de referencia');
        }

        $this->setDefaultCondition(self::COND_EQUALS);
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
        $options[self::COND_EQUALS] = 'Igual';
        $options[self::COND_NOT_EQUALS] = 'Diferente';
        $options[self::COND_NULL_OR_EMPTY] = 'Vazio';
        $options[self::COND_NOT_NULL_OR_EMPTY] = 'Não vazio';

        return $options;
    }

    public function getInputValue($index = 0)
    {
        $columnValue = $this->getValueName();
        $class = 'filterInput reference';
        $value = $this->getFilterValue($index);
        $field = new \View\Ext\ReferenceField($this->dbColumn, $columnValue, $value, $class);
        $field->setName($field->getName() . '[]');

        $field->onPressEnter("$('#buscar').click()");

        return $field;
    }

    public function createWhere($index = 0)
    {
        $column = $this->getColumn();
        $columnName = $column ? $column->getSql() : $this->getFilterName();
        $dbColumn = $this->getDbColumn();

        $filterName = $this->getValueName();
        $conditionValue = $this->getConditionValue($index);
        $filterValue = $this->getFilterValue($index);
        $conditionType = $index > 0 ? \Db\Cond::COND_OR : \Db\Cond::COND_AND;
        $hasFilter = (strlen($filterValue) > 0);

        //no condition selected, does nothing
        if (!$conditionValue || !$hasFilter)
        {
            return null;
        }
        else
        {
            $conditionValue = self::COND_EQUALS ? 'IN' : ' NOT IN';
            $searchQuery = str_replace(array(':filterCond?', ':filterValue?'), array($conditionValue, $filterValue), $dbColumn->getSearchQuery());
            $where = new \Db\Where($searchQuery);
            $where->setCondition($conditionType);

            return $where;
        }
    }

}
