<?php

namespace Db;

class SmartFilter
{

    const DATA_TYPE_TEXT = 'text';
    const DATA_TYPE_NUMBER = 'number';
    const DATA_TYPE_DATE = 'date';
    const DATA_TYPE_UNKNOWN = 'date';

    protected $queryString;
    protected $modelClass;
    protected $columns;
    protected $conds;

    public function __construct($modelClass, $columns, $queryString)
    {
        $this->setModelClass($modelClass);
        $this->setColumns($columns);
        $this->setQueryString($queryString);
    }

    public function getQueryString()
    {
        return $this->queryString;
    }

    public function setQueryString($queryString)
    {
        $this->queryString = $queryString;
        return $this;
    }

    public function getModelClass()
    {
        return $this->modelClass;
    }

    public function setModelClass($modelClass)
    {
        $this->modelClass = $modelClass;
        return $this;
    }

    public function getColumns()
    {
        //get default modle columns if null
        if (is_null($this->columns))
        {
            $name = $this->getModelClass();
            $this->columns = $name::getColumns();
        }

        return $this->columns;
    }

    public function setColumns($columns)
    {
        $this->columns = $columns;
        return $this;
    }

    public function getConds()
    {
        return $this->conds;
    }

    public function setConds($conds)
    {
        if (!is_array($cons))
        {
            $conds = (array) $conds;
        }

        $this->conds = $conds;
        return $this;
    }

    public function addCond(\Db\Cond $cond)
    {
        $this->conds[] = $cond;

        return $this;
    }

    /**
     * Detect data type of undefined string
     *
     * @param string $indefinedData
     * @return string
     */
    public function detectDataType($indefinedData)
    {
        if (!$indefinedData || is_null($indefinedData))
        {
            return self::DATA_TYPE_UNKNOWN;
        }
        else
        {
            $firstLetter = $indefinedData[0];

            $lettersToRemove[] = '"';
            $lettersToRemove[] = '>';
            $lettersToRemove[] = '<';
            $lettersToRemove[] = '#';
            $lettersToRemove[] = '@';

            //remove first letter to avoid error to detected
            if (in_array($firstLetter, $lettersToRemove))
            {
                $indefinedData = trim(substr($indefinedData, 1));
            }

            if ($this->isNumber($indefinedData))
            {
                return self::DATA_TYPE_NUMBER;
            }
            else if ($this->isDate($indefinedData))
            {
                return self::DATA_TYPE_DATE;
            }
        }

        return self::DATA_TYPE_TEXT;
    }

    /**
     * Verify if is an number
     *
     * @param string $indefinedData
     * @return boolean
     */
    public function isNumber($indefinedData)
    {
        //remove reais
        $value = \Type\Decimal::treatValue($indefinedData);

        return is_numeric($value);
    }

    /**
     * Verify if is a date
     *
     * @param string $indefinedData
     * @return boolean
     */
    public function isDate($indefinedData)
    {
        $myDate = \Type\DateTime::get($indefinedData);
        return $myDate->isValid();
    }

    /**
     * Create filters
     *
     * @return array
     */
    public function createFilters()
    {
        $queryString = $this->getQueryString();

        //don't go any further withou and data to filter
        if (strlen(trim($queryString)) == 0)
        {
            return array();
        }

        $preparedFilters = explode(';', $queryString);

        foreach ($preparedFilters as $filter)
        {
            $filter = trim($filter);

            if ($filter)
            {
                $this->createFilter($filter);
            }
        }

        return $this->mergeFilters($this->conds);
    }

    protected function createFilter($filter)
    {
        $name = $this->getModelClass();
        $columns = $this->getColumns();
        $explode = explode('=', $filter);
        $columnSearch = '';

        if (count($explode) == 2)
        {
            $columnSearch = \Type\Text::get(trim($explode[0]))->toASCII()->toLower();
            $filter = trim($explode[1]);
        }

        $dataType = $this->detectDataType($filter);

        foreach ($columns as $column)
        {
            //if is to search some column, and is not current column, jump for next column
            if ($columnSearch)
            {
                $searchThisColumn = $columnSearch == \Type\Text::get($column->getName())->toASCII()->toLower() || $columnSearch == \Type\Text::get($column->getLabel())->toASCII()->toLower();

                if (!$searchThisColumn)
                {
                    continue;
                }
            }

            $type = $column->getType();
            $firstLetter = $filter[0];

            //espefic filter by id
            if ($firstLetter == '@' && $dataType == self::DATA_TYPE_NUMBER)
            {
                $pk = $name::getPrimaryKey();
                $this->conds[] = new \Db\Cond($pk->getName() . ' = ?', substr($filter, 1));

                //if is an id, only make filter by id
                continue;
            }
            else if (in_array($type, array(\Db\Column::TYPE_INTEGER, \Db\Column::TYPE_DECIMAL)) && $dataType == self::DATA_TYPE_NUMBER)
            {
                $this->filterByNumber($filter, $column);
            }
            else if (in_array($type, array(\Db\Column::TYPE_TIMESTAMP, \Db\Column::TYPE_DATETIME, \Db\Column::TYPE_DATE)) && $dataType == self::DATA_TYPE_DATE)
            {
                $this->filterByDate($filter, $column);
            }
            else if (in_array($type, array(\Db\Column::TYPE_BOOL, \Db\Column::TYPE_TINYINT)))
            {
                $this->filterByBool($filter, $column);
            }
            else if (in_array($type, array(\Db\Column::TYPE_TEXT, \Db\Column::TYPE_VARCHAR, \Db\Column::TYPE_CHAR)))
            {
                $this->filterByString($filter, $column);
            }

            if (in_array($type, array(\Db\Column::TYPE_INTEGER)) && $dataType == self::DATA_TYPE_TEXT)
            {
                if ($column->getReferenceDescription())
                {
                    $this->filterByReferenceDescription($filter, $column);
                }
                else if ($column->getConstantValues())
                {
                    $this->filterByContantValues($filter, $column);
                }
            }
        }
    }

    protected function getColumnQuery(\Db\Column $column)
    {
        $columnQuery = $column->getName();

        if ($column instanceof \Db\SearchColumn)
        {
            $columnQuery = '( SELECT ' . $column->getQuery() . ' )';
        }

        return $columnQuery;
    }

    protected function filterByDate($filter, \Db\Column $column)
    {
        $columnName = $column->getName();
        $myDate = \Type\Date::get($filter);
        $this->conds[] = new \Db\Cond('date(' . $columnName . ')' . ' = ?', $myDate, \Db\Cond::COND_OR);
    }

    protected function filterByString($filter, \Db\Column $column)
    {
        $columnQuery = $this->getColumnQuery($column);
        $firstLetter = $filter[0];
        $lastLetter = $filter[mb_strlen($filter) - 1];

        //like google
        if ($firstLetter == '"' and $lastLetter == '"')
        {
            $preparedSearch = mb_strtolower(mb_substr($filter, 1, mb_strlen($filter) - 2));
            $this->conds[] = new \Db\Cond($columnQuery . ' = lower(?)', $preparedSearch, \Db\Cond::COND_OR);
        }
        else
        {
            $this->conds[] = new \Db\Cond($columnQuery . ' like ?', str_replace(' ', '%', '%' . $filter . '%'), \Db\Cond::COND_OR);
        }
    }

    protected function filterByNumber($filter, \Db\Column $column)
    {
        $columnQuery = $this->getColumnQuery($column);
        $firstLetter = $filter[0];

        if ($firstLetter == '>' || $firstLetter == '<')
        {
            $preparedSearch = \Type\Decimal::get(mb_strtolower(mb_substr($filter, 1, mb_strlen($filter) - 1)));

            $this->conds[] = new \Db\Cond($columnQuery . ' ' . $firstLetter . '= ?', $preparedSearch->toDb(), \Db\Cond::COND_OR);
        }
        else
        {
            $preparedSearch = \Type\Money::get($filter);

            $this->conds[] = new \Db\Cond($columnQuery . ' = ?', $preparedSearch->toDb(), \Db\Cond::COND_OR);
        }
    }

    protected function filterByReferenceDescription($filter, \Db\Column $column)
    {
        //reference description
        $name = $this->getModelClass();
        $catalog = $name::getCatalogClass();
        $tableName = $catalog::parseTableNameForQuery($name::getTableName());
        $referenceClass = '\Model\\' . $column->getReferenceTable();
        $referenceTable = $catalog::parseTableNameForQuery($referenceClass::getTableName());

        $top = '';
        $limit = '';

        if (strtolower($catalog) == '\db\mssqlcatalog')
        {
            $top = 'TOP 1 ';
        }
        else
        {
            $limit = ' LIMIT 1 ';
        }

        $query = '( SELECT ' . $top . $column->getReferenceDescription() . ' FROM ' . $referenceTable . ' WHERE ' . $referenceTable . '.' . $column->getReferenceField() . ' = ' . $tableName . '.' . $column->getName() . ' ' . $limit . ') ';
        $this->conds[] = new \Db\Cond($query . ' like ?', str_replace(' ', '%', '%' . $filter . '%'), \Db\Cond::COND_OR);
    }

    protected function filterByContantValues($filter, \Db\Column $column)
    {
        $columnName = $column->getName();
        $filter = \Type\Text::get($filter)->toASCII()->toLower();
        $cValues = $column->getConstantValues();

        foreach ($cValues as $value => $info)
        {
            $info = \Type\Text::get($info)->toASCII()->toLower();

            if ($filter == $info)
            {
                $this->conds[] = new \Db\Cond($columnName . ' = ?', $value, \Db\Cond::COND_OR);
            }
        }
    }

    protected function filterByBool($filter, \Db\Column $column)
    {
        $columnName = $column->getName();
        $columnLabel = $column->getLabel();

        if ($filter == $columnName || $filter == $columnLabel)
        {
            $this->conds[] = new \Db\Cond($columnName . ' = ' . \Db\Catalog::DB_TRUE, NULL, \Db\Cond::COND_OR);
        }
    }

    /**
     * Merge filters in one \Db\Cond to make extra filters work
     *
     *
     * @param array $where
     * @return array
     */
    protected function mergeFilters($where)
    {
        $filters = array();
        $filtersHaving = array();

        //force empty query for zero return
        if (( is_array($where) && count($where) == 0 ) || !is_array($where))
        {
            $filters[] = new \Db\Cond('1=0');
            return $filters;
        }

        $count = 0;
        $values = array();

        $whereString = '(';

        foreach ($where as $cond)
        {
            if ($cond->getType() == \Db\Cond::TYPE_HAVING)
            {
                $filtersHaving[] = $cond;
            }
            else
            {
                $whereString .= $cond->getWhere($count === 0);
                $values = array_merge($values, $cond->getValue());
                $count++;
            }
        }

        $whereString .= ')';

        $filters[] = new \Db\Cond($whereString, $values, \Db\Cond::COND_AND);

        return array_merge($filters, $filtersHaving);
    }

}