<?php

namespace Db;

class QueryBuilder
{

    protected $catalogClass = '\Db\MysqlCatalog';
    protected $modelName = '';
    protected $conn;
    protected $tableName;
    protected $join = null;
    protected $columns = ['*'];
    protected $where = null;
    protected $limit = null;
    protected $offset = null;
    protected $orderBy = null;

    public function __construct($tableName = null, $catalog = '\Db\MysqlCatalog', $connInfoId = 'default')
    {
        //mysql for default
        $this->setCatalogClass($catalog ? $catalog : '\Db\MysqlCatalog');
        $this->setConn(\Db\Conn::getInstance($connInfoId ? $connInfoId : 'default'));
        $this->setTableName($tableName);
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
            $this->tableName = $catalog::parseTableNameForQuery($tableName);
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

    public function getWhere()
    {
        return $this->where;
    }

    public function setWhere($where)
    {
        $this->where = $where;
        return $this;
    }

    /**
     * Add a where object, can be \Db\Where or \Db\Cond
     * or any class that implements getWhere and getWhereSel methods
     * Or array of this classes.
     *
     * @param type $where
     * @return $this
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

    public function where($columnName, $param, $value = NULL, $condition = 'AND')
    {
        //support only two parameters
        if (!$value)
        {
            $value = $param;

            if (is_array($value))
            {
                $param = 'IN';
            }
            else
            {
                $param = '=';
            }
        }

        $catalog = $this->catalogClass;
        $columnName = $catalog::parseTableNameForQuery($columnName);

        $where = new \Db\Where($columnName, $param, $value, $condition ? $condition : 'AND');

        $this->where[] = $where;

        return $this;
    }

    public function whereOr($columnName, $param, $value)
    {
        return $this->where($columnName, $param, $value, 'OR');
    }

    /* public function whereRaw($where, $operation = 'AND')
      {
      $operation = $operation ? trim(strtoupper($operation)) : 'AND';
      $this->where[] = $operation . $where;
      } */

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

    public function getSelectSql($format = false)
    {
        $catalog = $this->getCatalogClass();
        $whereStd = \Db\Model::getWhereFromFilters($this->where);
        $where = $whereStd->sqlParam;

        return $catalog::mountSelect($this->getTables($format), $this->mountColumns($format), $where, $this->getLimit(), $this->getOffset(), NULL, NULL, $this->mountOrderBy(), NULL, $format);
    }

    public function select($returnAs)
    {
        $catalog = $this->getCatalogClass();
        $whereStd = \Db\Model::getWhereFromFilters($this->where);
        $where = $whereStd->sql;
        $sql = $catalog::mountSelect($this->getTables(), $this->mountColumns(TRUE), $where, $this->getLimit(), $this->getOffset(), NULL, NULL, $this->mountOrderBy(), NULL, TRUE);

        return $this->getConn()->query($sql, $whereStd->args, $returnAs);
    }

    public function update()
    {
        throw new Exception('Not implemented yet!');
    }

    public function delete()
    {
        throw new Exception('Not implemented yet!');
    }

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

    public function toCollection()
    {
        //collection by default use objects
        $modelName = $this->getModelName() ? $this->getModelName() : 'stdClass';
        return new \Db\Collection($this->select($modelName));
    }

    public function toArray()
    {
        return $result = $this->select('array');
    }

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

}
