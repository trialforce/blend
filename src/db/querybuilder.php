<?php

namespace Db;

/**
 * Database Query Builder
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
    protected $orderBy = null;

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
        $this->connInfoId = $connInfoId ? $connInfoId : 'default';
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

    function join($type, $tableName, $on, $alias = NULL)
    {
        $this->join[] = new \Db\Join($type, $tableName, $on, $alias);
        return $this;
    }

    function leftJoin($tableName, $on, $alias = NULL)
    {
        $this->join[] = new \Db\Join('left', $tableName, $on, $alias);

        return $this;
    }

    function rightJoin($tableName, $on, $alias = NULL)
    {
        $this->join[] = new \Db\Join('right', $tableName, $on, $alias);

        return $this;
    }

    function innerJoin($tableName, $on, $alias = NULL)
    {
        $this->join[] = new \Db\Join('inner', $tableName, $on, $alias);

        return $this;
    }

    function fullJoin($tableName, $on, $alias = NULL)
    {
        $this->join[] = new \Db\Join('full', $tableName, $on, $alias);

        return $this;
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
     * @param type $columns
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
        $where = new \Db\Where($columnName, $param, $value, $condition ? $condition : 'AND');
        $this->where[] = $where;

        return $this;
    }

    /**
     * Add a compare condition to where list
     *
     * @param string $columnName the column name
     * @param string $param the condition param =, IN , >= etc
     * @param string $value the field that is to be compared
     * @param string $condition AND, OR, etc
     * @return \Db\QueryBuilder
     */
    public function compare($columnName, $param = NULL, $compare = NULL, $condition = 'AND')
    {
        $catalog = $this->catalogClass;
        $columnName = $catalog::parseTableNameForQuery($columnName);
        $where = new \Db\Compare($columnName, $param, $compare, $condition ? $condition : 'AND');
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

                $tables .= $join->getSql();
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

        $select = $catalog::mountSelect($this->getTables($format), $this->mountColumns($format), $where, $this->getLimit(), $this->getOffset(), $this->getGroupBy(), NULL, $this->mountOrderBy(), NULL, $format);
        return new \Type\Text($select);
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
        $sql = $catalog::mountSelect($this->getTables(), $this->mountColumns(TRUE), $where, $this->getLimit(), $this->getOffset(), $this->getGroupBy(), NULL, $this->mountOrderBy(), NULL, TRUE);

        return $this->getConn()->query($sql, $whereStd->getArgs(), $returnAs);
    }

    /**
     * Execute the query and return
     * the first element of the result
     *
     * @return mixed
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
    }

    /**
     * Return the first register or a new one
     *
     * @param boolen $fillDataInWhere if is to fill data in where
     *
     * @return \Db\Model
     */
    public function firstOrCreate($fillDataInWhere = false)
    {
        $first = $this->first();

        if (!$first)
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
     * @return \Db\Collection the collection with the resulted data
     */
    public function toCollection()
    {
        //collection by default use objects
        $modelName = $this->getModelName() ? $this->getModelName() : 'stdClass';
        return new \Db\Collection($this->select($modelName));
    }

    /**
     * Return data as an array of array
     * @return array array of array
     */
    public function toArray()
    {
        return $result = $this->select('array');
    }

    /**
     * Return data as array of stdClass
     *
     * @return array array of stdClass
     */
    public function toArrayStdClass()
    {
        return $result = $this->select('StdClass');
    }

    /**
     * Simple method to make an aggregation
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

    public function update()
    {
        throw new Exception('Not implemented yet!');
    }

    public function delete()
    {
        throw new Exception('Not implemented yet!');
    }

}
