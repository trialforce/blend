<?php

namespace Db;

/**
 * Smart Filter, simulate a "Google", detecting searched string
 * and database structered data.
 *
 * @todo I think this need to be refatored, document and optimized
 */
class SmartFilter
{

    /**
     * Data type text
     */
    const DATA_TYPE_TEXT = 'text';

    /**
     * Data type number
     */
    const DATA_TYPE_NUMBER = 'number';

    /**
     * Data type date
     */
    const DATA_TYPE_DATE = 'date';

    /**
     * Data type unknown
     */
    const DATA_TYPE_UNKNOWN = 'unknown';

    protected $queryString;
    protected $modelClass;
    protected $columns;
    protected $conds;

    public function __construct($modelClass, $columns, $queryString = NULL)
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
        //get default model columns if null
        if (is_null($this->columns) || (is_array($this->columns) && count($this->columns) == 0))
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

        //don't go any further without and data to filter
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
            else if (in_array($type, array(\Db\Column\Column::TYPE_INTEGER, \Db\Column\Column::TYPE_DECIMAL)) && $dataType == self::DATA_TYPE_NUMBER)
            {
                $this->filterByNumber($filter, $column);
            }
            else if (in_array($type, array(\Db\Column\Column::TYPE_TIMESTAMP, \Db\Column\Column::TYPE_DATETIME, \Db\Column\Column::TYPE_DATE)) && $dataType == self::DATA_TYPE_DATE)
            {
                $this->filterByDate($filter, $column);
            }
            else if (in_array($type, array(\Db\Column\Column::TYPE_BOOL, \Db\Column\Column::TYPE_TINYINT)))
            {
                $this->filterByBool($filter, $column);
            }
            else if (in_array($type, array(\Db\Column\Column::TYPE_TEXT, \Db\Column\Column::TYPE_VARCHAR, \Db\Column\Column::TYPE_CHAR)))
            {
                $this->filterByString($filter, $column);
            }

            if (in_array($type, array(\Db\Column\Column::TYPE_INTEGER)) && $dataType == self::DATA_TYPE_TEXT)
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

    protected function getColumnQuery(\Db\Column\Column $column)
    {
        $className = $this->getModelClass();
        $catalog = $className::getCatalogClass();
        $tableName = $catalog::parseTableNameForQuery($className::getTableName());

        $columnQuery = $column->getName();

        if ($column instanceof \Db\Column\Search)
        {
            $columnQuery = '( SELECT ' . $column->getQuery() . ' )';
        }
        else if ($tableName)
        {
            $columnQuery = $tableName . '.' . $column->getName();
        }

        return $columnQuery;
    }

    protected function filterByDate($filter, \Db\Column\Column $column)
    {
        $columnName = $column->getName();
        $myDate = \Type\Date::get($filter);
        $this->conds[] = new \Db\Cond('date(' . $columnName . ')' . ' = ?', $myDate, \Db\Cond::COND_OR);
    }

    protected function filterByString($filter, \Db\Column\Column $column)
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
            $excecoes = \Type\Text::listExceptionWords();

            foreach ($excecoes as $excecao)
            {
                $filter = str_ireplace($excecao, ' ', $filter);
            }

            //calculate singular words
            $filtersingular = '';
            $words = explode(' ', $filter);

            foreach ($words as $word)
            {
                $singular = \Type\Text::toSingular($word);
                $filtersingular .= ' ' . $singular;
            }

            $where = '';

            //pass trough words creating the filter
            foreach ($words as $word)
            {
                $where .= (!$where) ? '' : ' and ';
                $where .= $columnQuery . " like '%" . trim($word) . "%' ";
            }

            $this->conds[] = new \Db\Where("($where)", NULL, NULL, \Db\Cond::COND_OR);

            //if singular filter is diferent from plural ones
            if ($filter != $filtersingular)
            {
                $whereSingular = '';
                $wordsSingular = explode(' ', trim($filtersingular));

                foreach ($wordsSingular as $word)
                {
                    $whereSingular .= (!$whereSingular) ? '' : ' and ';
                    $whereSingular .= $columnQuery . " like '%" . trim($word) . "%' ";
                }

                $this->conds[] = new \Db\Where("($whereSingular)", NULL, NULL, \Db\Cond::COND_OR);
            }
        }
    }

    protected function filterByNumber($filter, \Db\Column\Column $column)
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

    protected function filterByReferenceDescription($filter, \Db\Column\Column $column)
    {
        //reference description
        $name = $this->getModelClass();
        $catalog = $name::getCatalogClass();
        $tableName = $catalog::parseTableNameForQuery($name::getTableName());
        $referenceClass = $column->getReferenceModelClass();
        $referenceTable = $catalog::parseTableNameForQuery($referenceClass::getTableName());

        $top = '';
        $limit = '';

        if (strtolower($catalog) == '\db\catalog\mssql')
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

    protected function filterByContantValues($filter, \Db\Column\Column $column)
    {
        $columnSql = $column->getSql(FALSE);
        $filter = \Type\Text::get($filter)->toASCII()->toLower();
        $cValues = $column->getConstantValues();

        foreach ($cValues as $value => $info)
        {
            $info = \Type\Text::get($info)->toASCII()->toLower();

            if ($filter == $info)
            {
                $this->conds[] = new \Db\Where($columnSql[0], '=', $value, \Db\Cond::COND_OR);
            }
        }
    }

    protected function filterByBool($filter, \Db\Column\Column $column)
    {
        $columnName = $column->getName();
        $columnLabel = $column->getLabel();

        if ($filter == $columnName || $filter == $columnLabel)
        {
            $this->conds[] = new \Db\Cond($columnName . ' = ' . \Db\Catalog\Mysql::DB_TRUE, NULL, \Db\Cond::COND_OR);
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
            $cond instanceof \Db\Cond;

            $whereString .= $cond->getWhere($count === 0);
            $value = $cond->getValue();
            $values = array_merge($values, is_array($value) ? $value : array());
            $count++;
        }

        $whereString .= ')';

        $filters[] = new \Db\Cond($whereString, $values, \Db\Cond::COND_AND);

        return $filters;
    }

}
