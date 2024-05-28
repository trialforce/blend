<?php

namespace Db;

/**
 * Database Query Builder
 *
 * @template T
 */
class QueryBuilder
{

    protected $catalogClass = '\Db\MysqlCatalog';
    protected $connInfoId = 'default';
    protected $modelName = '';
    protected $conn;
    protected $tableName;
    protected $join = null;
    protected $columns = ['*'];
    protected $where = null;
    protected $limit = null;
    protected $offset = null;
    protected $groupBy = null;
    protected $having = null;
    protected $orderBy = null;
    protected $logId = null;

    /**
     * Construct the query builder
     *
     * @param string $tableName main table name
     * @param string $connInfoId connection identification
     */
    public function __construct($tableName = null, $connInfoId = 'default')
    {
        $this->setConnInfoId($connInfoId);
        $this->setTableName($tableName);
    }

    /**
     * An static alias to construct
     *
     * @param string $tableName main table name
     * @param string $connInfoId connection identification
     * @return \Db\QueryBuilder
     */
    public static function create($tableName = null, $connInfoId = 'default')
    {
        return new \Db\QueryBuilder($tableName, $connInfoId);
    }

    public function setConnInfoId($connInfoId = null)
    {
        $this->connInfoId = $connInfoId ?? 'default';
        $conn = \Db\Conn::getInstance($this->connInfoId);
        $connInfo = \Db\Conn::getConnInfo($this->connInfoId);

        $this->setConn($conn);
        $this->setCatalogClass($connInfo->getCatalogClass());

        return $this;
    }

    public function getConnInfoId()
    {
        return $this->connInfoId;
    }

    public function getCatalogClass()
    {
        return $this->catalogClass;
    }

    public function setCatalogClass($catalogClass)
    {
        $this->catalogClass = $catalogClass;
        return $this;
    }

    public function getConn()
    {
        return $this->conn;
    }

    public function setConn($conn)
    {
        $this->conn = $conn;
        return $this;
    }

    public function getTableName()
    {
        return $this->tableName;
    }

    public function setTableName($tableName)
    {
        if ($tableName)
        {
            $catalog = $this->catalogClass;
            $prefix = \DataHandle\Config::get('dbprefix-' . $this->getConnInfoId());
            $this->tableName = $catalog::parseTableNameForQuery($prefix . $tableName);
        }

        return $this;
    }

    public function getJoin()
    {
        return $this->join;
    }

    public function setJoin($join)
    {
        $this->join = $join;
        return $this;
    }

    /**
     * Add a external join object
     * You can add a simple string or any object that has __toString method
     * This methods allow that to support SqlServer Outer Apply.
     *
     * @param \Db\Join $join
     * @return $this
     */
    function addJoin($join)
    {
        $this->join[] = $join;

        return $this;
    }

    /**
     * Add a join to the query
     *
     * @param string $type the type of the join, left, right, inner, full etc
     * @param string $tableName the name of the table
     * @param string $on the relation between the two tables
     * @param string $alias as lias to the joined table, if needed
     * @return $this
     */
    function join($type, $tableName, $on, $alias = NULL)
    {
        $this->join[] = new \Db\Join($type, $tableName, $on, $alias);
        return $this;
    }

    /**
     * Add a left join the the query
     *
     * @param string $tableName the name of the table
     * @param string $on the relation between the two tables
     * @param string $alias as lias to the joined table, if needed
     *
     * @return $this
     */
    function leftJoin($tableName, $on, $alias = NULL)
    {
        $this->join[] = new \Db\Join('left', $tableName, $on, $alias);

        return $this;
    }

    /**
     * Add a right join to the query
     *
     * @param string $tableName the name of the table
     * @param string $on the relation between the two tables
     * @param string $alias as lias to the joined table, if needed
     * @return $this
     */
    function rightJoin($tableName, $on, $alias = NULL)
    {
        $this->join[] = new \Db\Join('right', $tableName, $on, $alias);

        return $this;
    }

    /**
     * Add a inner join to the query
     *
     * @param string $tableName the name of the table
     * @param string $on the relation between the two tables
     * @param string $alias as lias to the joined table, if needed
     * @return $this
     */
    function innerJoin($tableName, $on, $alias = NULL)
    {
        $this->join[] = new \Db\Join('inner', $tableName, $on, $alias);

        return $this;
    }

    /**
     * Add a full join to the query
     *
     * @param string $tableName the name of the table
     * @param string $on the relation between the two tables
     * @param string $alias as lias to the joined table, if needed
     * @return $this
     */
    function fullJoin($tableName, $on, $alias = NULL)
    {
        $this->join[] = new \Db\Join('full', $tableName, $on, $alias);

        return $this;
    }

    /**
     * Verify if some join exists, by table name
     * @param string $tableName table name
     * @return boolean
     */
    function joinExistsTable($tableName)
    {
        if (!is_array($this->join))
        {
            return false;
        }

        foreach ($this->join as $join)
        {
            if ($join->getTableName() == $tableName)
            {
                return true;
            }
        }

        return false;
    }

    /**
     * Verify if some join exists by alias
     * @param string $alias alias
     * @return boolean
     */
    function joinExistsAlias($alias)
    {
        if (!is_array($this->join))
        {
            return false;
        }

        foreach ($this->join as $join)
        {
            if ($join->getAlias() == $alias)
            {
                return true;
            }
        }

        return false;
    }

    function getModelName()
    {
        return $this->modelName;
    }

    function setModelName($modelName)
    {
        $this->modelName = $modelName;

        return $this;
    }

    public function getLimit()
    {
        return $this->limit;
    }

    public function getOffset()
    {
        return $this->offset;
    }

    public function setLimit($limit)
    {
        $this->limit = $limit;
        return $this;
    }

    public function limit($limit, $offset = NULL)
    {
        $this->setOffset($offset);
        return $this->setLimit($limit);
    }

    public function setOffset($offset)
    {
        $this->offset = $offset;
        return $this;
    }

    public function Offset($offset)
    {
        return $this->setOffset($offset);
    }

    public function getColumns()
    {
        return $this->columns;
    }

    public function setColumns($columns)
    {
        $this->columns = $columns;
        return $this;
    }

    /**
     * Define add column to query
     *
     * @param array|string $columns
     * @return \Db\QueryBuilder
     */
    public function columns($columns)
    {
        $args = func_get_args();

        //if has 2 or more args, they are the column array
        if (count($args) > 1)
        {
            $columns = $args;
        }
        //if is a string separated by comma, explode it
        else if (is_string($columns) && stripos($columns, ','))
        {
            $columns = explode(',', $columns);
        }

        $this->columns = $columns;

        return $this;
    }

    /**
     * Add a column to query
     *
     * @param string $columnName column name
     * @param string $alias column alias
     * @return $this
     */
    public function column($columnName, $alias = NULL)
    {
        $catalog = $this->catalogClass;

        if (!is_array($this->columns))
        {
            $this->columns = [$this->columns];
        }

        if ($alias)
        {
            $columnName = $catalog::parseTableNameForQuery($columnName) . ' AS ' . $catalog::parseTableNameForQuery($alias);
        }
        else
        {
            $columnName = $catalog::parseTableNameForQuery($columnName);
        }

        $this->columns[] = $columnName;
        return $this;
    }

    /**
     * Add a raw column to query
     * @param string $columnName column name/sql
     * @return $this
     */
    public function columnRaw($columnName)
    {
        $this->columns[] = $columnName;
        return $this;
    }

    public function getGroupBy()
    {
        return $this->groupBy;
    }

    public function setGroupBy($groupBy)
    {
        $this->groupBy = $groupBy;
        return $this;
    }

    /**
     * Make an group by in query
     *
     * @param mixed $groupBy string, array, or multiple method args
     * @return $this
     */
    public function groupBy($groupBy)
    {
        $args = func_get_args();

        //if method has 2 or more args, so consider it like an array
        if (count($args) > 1)
        {
            $groupBy = $args;
        }

        //if is an array, glue it by comma
        if (is_array($groupBy))
        {
            $groupBy = implode(',', $groupBy);
        }

        //if allready has some group by, add an comma to make command work
        if ($this->groupBy)
        {
            $groupBy = $groupBy . ',';
        }

        $this->groupBy .= $groupBy;
        return $this;
    }

    public function getLogId()
    {
        return $this->logId;
    }

    public function setLogId($logId)
    {
        $this->logId = $logId;
        return $this;
    }

    public function getOrderBy()
    {
        return $this->orderBy;
    }

    public function setOrderBy($orderBy)
    {
        $this->orderBy = $orderBy;
        return $this;
    }

    public function clearOrderBy()
    {
        $this->orderBy = null;

        return $this;
    }

    public function orderBy($orderBy, $orderWay = NULL)
    {
        return $this->addOrderBy($orderBy, $orderWay);
    }

    public function addOrderBy($orderBy, $orderWay = NULL)
    {
        $catalog = $this->catalogClass;

        if (!$orderBy)
        {
            return $this;
        }

        if ($this->orderBy && !is_array($this->orderBy))
        {
            $this->orderBy = [$this->orderBy];
        }

        $orderBy = $catalog::parseTableNameForQuery($orderBy);

        if ($orderWay)
        {
            $orderBy .= ' ' . strtoupper($orderWay);
        }

        $this->orderBy[] = $orderBy;
        return $this;
    }

    public function having($condition)
    {
        $this->having .= $condition;

        return $this;
    }

    public function getHaving()
    {
        return $this->having;
    }

    public function setHaving($having)
    {
        $this->having = $having;
        return $this;
    }

    /**
     * Return all condition\where in query builder
     *
     * @return array
     */
    public function getWhere()
    {
        return $this->where;
    }

    /**
     * Return the where with search column sql parsed
     * @return array the where filters array
     */
    public function getParsedWhere()
    {
        return \Db\Model::parseSearchColumnWhere($this->getWhere(), $this->getModelName());
    }

    /**
     * Define/Overwrite the condition/where array
     *
     * Note that condition can be grouped by an array
     *
     * @param array $where
     * @return \Db\QueryBuilder
     */
    public function setWhere($where)
    {
        $this->where = $where;
        return $this;
    }

    public function clearWhere()
    {
        $this->where = [];
        return $this;
    }

    /**
     * Add a where object, can be \Db\Where or \Db\Cond ( \Db\Filter )
     * or any class that implements getWhere and getWhereSel methods
     * Or array of this classes.
     *
     * @param mixed $where
     * @return \Db\QueryBuilder
     */
    public function addWhere($where)
    {
        if (is_array($where))
        {
            $myWhere = $this->where;

            //convert to array if needed
            if (!is_array($this->where))
            {
                if ($this->where)
                {
                    $myWhere[] = $this->where;
                }
                else //clean array
                {
                    $myWhere = array();
                }
            }

            $this->where = array_merge($myWhere, $where);
        }
        else
        {
            $this->where[] = $where;
        }

        return $this;
    }

    /**
     * Add a where condition to where list
     *
     * @param string $columnName the column name
     * @param string $param the condition param =, IN , >= etc
     * @param string $value the filtered value
     * @param string $condition AND, OR, etc
     * @return \Db\QueryBuilder
     */
    public function where($columnName, $param = NULL, $value = NULL, $condition = 'AND')
    {
        $catalog = $this->catalogClass;
        $columnName = $catalog::parseTableNameForQuery($columnName);
        $where = new \Db\Where($columnName, $param, $value, $condition ?? 'AND');
        $this->where[] = $where;

        return $this;
    }

    /**
     * Add a where condition to the were list, but only if a value is passed
     *
     * @param string $columnName the column name
     * @param string $param the condition param =, IN , >= etc
     * @param string $value the filtered value
     * @param string $condition AND, OR, etc
     * @return \Db\QueryBuilder
     */
    public function whereIf($columnName, $param = NULL, $value = NULL, $condition = 'AND')
    {
        if ($value || $value === 0 || $value === '0')
        {
            return $this->where($columnName, $param, $value, $condition);
        }

        return $this;
    }

    /**
     * Add a compare condition to where list
     *
     * @param string $columnName the column name
     * @param string $param the condition param =, IN , >= etc
     * @param string $compare the field that is to be compared
     * @param string $condition AND, OR, etc
     * @return \Db\QueryBuilder
     */
    public function compare($columnName, $param = NULL, $compare = NULL, $condition = 'AND')
    {
        $catalog = $this->catalogClass;
        $columnName = $catalog::parseTableNameForQuery($columnName);
        $where = new \Db\Compare($columnName, $param, $compare, $condition ?? 'AND');
        $this->where[] = $where;

        return $this;
    }

    /**
     * Add an "AND" condition
     *
     * @param string $columnName column name
     * @param string $param the condicition param (=, in, >=)
     * @param string $value the filter value
     * @return \Db\QueryBuilder
     */
    public function and($columnName, $param = null, $value = NULL)
    {
        return $this->where($columnName, $param, $value, 'AND');
    }

    public function andIf($columnName, $param = NULL, $value = NULL, $condition = 'AND')
    {
        if ($value || $value === 0 || $value === '0')
        {
            return $this->where($columnName, $param, $value, $condition);
        }

        return $this;
    }

    /**
     * Add an "OR" condition
     *
     * @param string $columnName column name
     * @param string $param the condicition param (=, in, >=)
     * @param string $value the filter value
     * @return \Db\QueryBuilder
     */
    public function or($columnName, $param = NULL, $value = NULL)
    {
        return $this->where($columnName, $param, $value, 'OR');
    }

    /**
     * Create a filter group
     *
     * @param string $columnName column name
     * @param string $param the condicition param (=, in, >=)
     * @param string $value the filter value
     *
     * @return \Db\Criteria
     */
    public function groupWhere($columnName, $param = NULL, $value = NULL, $condition = 'AND')
    {
        $group = new \Db\Criteria();
        $group->where($columnName, $param, $value, $condition);
        $group->setCloseObject($this);
        $this->where[] = $group;

        return $group;
    }

    protected function mountColumns($format = false)
    {
        $columns = $this->getColumns();

        if (is_array($columns))
        {
            $explode = $format ? ", \r\n" : ', ';
            $columns = implode($explode, $columns);
        }

        return $columns;
    }

    protected function mountOrderBy()
    {
        $orders = $this->getOrderBy();

        if (is_array($orders))
        {
            $orders = implode(', ', $orders);
        }

        return $orders;
    }

    /**
     * Create and return the "tables" part of the query
     * Including joins
     *
     * @param bool $format true if is to format the query
     * @return string the tables part of the query
     */
    protected function getTables($format = FALSE)
    {
        $tables = $this->getTableName();

        if ($this->join)
        {
            $joins = is_array($this->join) ? $this->join : [$this->join];

            foreach ($joins as $join)
            {
                if ($format)
                {
                    $tables .= "\r\n";
                }

                $tables .= ' ' . $join;
            }
        }

        return $tables;
    }

    /**
     * Return the string of sql
     *
     * @param bool $format formated or in one line
     * @return \Type\Text the select for the query
     */
    public function getSelectSql($format = false)
    {
        $catalog = $this->getCatalogClass();
        $whereStd = \Db\Criteria::createCriteria($this->getParsedWhere());
        $where = $whereStd->getSqlParam();

        $select = $catalog::mountSelect($this->getTables($format), $this->mountColumns($format), $where, $this->getLimit(), $this->getOffset(), $this->getGroupBy(), $this->getHaving(), $this->mountOrderBy(), NULL, $format);
        return $select;
    }

    /**
     * Execute the query and return it as you need
     *
     * @param string $returnAs array, stdClass or any class you want
     * @return array array of $returnAs
     */
    public function select($returnAs)
    {
        $catalog = $this->getCatalogClass();
        $whereStd = \Db\Criteria::createCriteria($this->getParsedWhere());
        $where = $whereStd->getSql();
        $sql = $catalog::mountSelect($this->getTables(), $this->mountColumns(TRUE), $where, $this->getLimit(), $this->getOffset(), $this->getGroupBy(), $this->getHaving(), $this->mountOrderBy(), NULL, TRUE);

        return $this->getConn()->query($sql, $whereStd->getArgs(), $returnAs, $this->logId);
    }

    /**
     * Execute the query and return
     * the first element of the result
     *
     * @return T|null
     */
    public function first()
    {
        $this->setLimit(1);
        $this->setOffset(NULL);

        $result = $this->select($this->getModelName());

        if ($result)
        {
            return $result[0];
        }

        return null;
    }

    /**
     * Return the first register or a new one
     *
     * @param boolean $fillDataInWhere if is to fill data in where
     *
     * @return T
     */
    public function firstOrCreate($fillDataInWhere = false)
    {
        $first = $this->first();

        if (!$first && $this->getModelName())
        {
            $className = $this->getModelName();
            $first = new $className();

            if ($fillDataInWhere)
            {
                $this->fillDataInWhere($first);
            }
        }

        return $first;
    }

    /**
     * Fill the data filtred with equal in where in the passed model
     *
     * @param \Db\Model $model
     * @return $this
     */
    protected function fillDataInWhere($model)
    {
        $wheres = $this->getWhere();

        foreach ($wheres as $where)
        {
            if ($where instanceof \Db\Where)
            {
                if ($where->getParam() === '=')
                {
                    $values = $where->getValue();
                    $propertyName = \Db\Column\Column::getRealColumnName($where->getFilter());

                    //TODO put this in right place
                    if ($this->getCatalogClass() == '\Db\Catalog\Mysql')
                    {
                        $propertyName = str_replace('`', '', $propertyName);
                    }

                    $model->setValue($propertyName, $values[0]);
                }
            }
        }

        return $this;
    }

    /**
     * Return a collection of the defined model name
     *
     * @return \Db\Collection<T> the collection with the resulted data
     */
    public function toCollection()
    {
        //collection by default use objects
        $modelName = $this->getModelName() ?? 'stdClass';
        return new \Db\Collection($this->select($modelName));
    }

    /**
     * @return \Db\Collection<\stdClass>
     */
    public function toCollectionStdClass()
    {
        return new \Db\Collection($this->select('stdClass'));
    }

    /**
     * Return data as an array of array
     * @return array<T> array of array
     */
    public function toArray()
    {
        return $this->select('array');
    }

    /**
     * Return data as array of stdClass
     *
     * @return array array of stdClass
     */
    public function toArrayStdClass()
    {
        return $this->select('StdClass');
    }

    /**
     * Execute an aggregation in database
     * sum, max, min, avg, count
     *
     * @param string $method
     * @param string $property
     * @return int
     */
    public function aggr($method, $property)
    {
        return $this->aggregation($method . '(' . $property . ')');
    }

    /**
     * Execute an aggregation in database
     *
     * @param string $aggr the aggregation, Ex.: SUM(price)
     * @return mixed
     */
    public function aggregation($aggr)
    {
        //aggregation does not have order by
        $this->clearOrderBy();
        $this->limit(null, null);
        $this->setColumns($aggr . ' AS aggregation');

        $result = $this->select('array');

        if (isset($result[0]) && isset($result[0]['aggregation']))
        {
            return $result[0]['aggregation'];
        }

        return NULL;
    }

    /**
     * Simple method that return the count of all registers in query
     *
     * @return int
     */
    public function count($column = '*')
    {
        return $this->aggregation('count(' . $column . ')');
    }

    /**
     * Bulk update register in database
     *
     * @param array $values the key indexed values array
     * @return int 1 for okay
     */
    public function update(array $values)
    {
        //without value to update, does nothing
        if (!$values || count($values) == 0)
        {
            return false;
        }

        //clean join because you can't have a update with join, right?
        $this->join = null;
        $catalog = $this->getCatalogClass();
        $whereStd = \Db\Criteria::createCriteria($this->getParsedWhere());

        $columns = [];
        $dbValues = [];

        foreach ($values as $columnName => $value)
        {
            if ($value instanceof \Type\Generic)
            {
                $value = $value->toDb();
            }

            $dbValues[] = $value;

            $columns[] = $catalog ::parseTableNameForQuery($columnName) . ' = ?';
        }

        $sql = $catalog ::mountUpdate($this->getTables(false), implode(',', $columns), $whereStd->getSql());
        $args = array_merge($dbValues, $whereStd->getArgs());

        return $this->getConn()->execute($sql, $args);
    }

    /**
     * Bulk delete register in database.
     *
     * @return int 1 for okay
     */
    public function delete()
    {
        //clear join because you can't have a delete with join
        $this->join = null;
        $catalog = $this->getCatalogClass();
        $whereStd = \Db\Criteria::createCriteria($this->getParsedWhere());

        $sql = $catalog::mountDelete($this->getTables(false), $whereStd->getSql());

        return $this->getConn()->execute($sql, $whereStd->getArgs());
    }

}
