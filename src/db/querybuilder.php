<?php

namespace Db;

class QueryBuilder
{

    protected $tableName;
    protected $columns = ['*'];
    protected $where = null;
    protected $limit = null;
    protected $offset = null;
    protected $orderBy = null;
    protected $catalog;
    protected $conn;

    public function __construct($tableName)
    {
        //mysql for default
        $this->setCatalog(new \Db\MysqlCatalog());
        $this->setConn(\Db\Conn::getInstance('default'));
        $this->setTableName($tableName);
    }

    public function getCatalog()
    {
        return $this->catalog;
    }

    public function setCatalog($catalog)
    {
        $this->catalog = $catalog;
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
            $catalog = get_class($this->getCatalog());
            $this->tableName = $catalog::parseTableNameForQuery($tableName);
        }

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

    public function setOffset($offset)
    {
        $this->offset = $offset;
        return $this;
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
        $catalog = get_class($this->getCatalog());

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

    public function orderBy($orderBy, $orderWay = NULL)
    {
        if (!is_array($this->orderBy))
        {
            $this->orderBy = [$this->orderBy];
        }

        $catalog = get_class($this->getCatalog());

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

    public function where($columnName, $param, $value = NULL, $operation = 'AND')
    {
        $catalog = get_class($this->getCatalog());
        $operation = $operation ? trim(strtoupper($operation)) : 'AND';

        if (!is_array($this->where))
        {
            $this->where = [$this->where];
        }

        //add suport for two parameters
        if (!$value)
        {
            $value = $param;

            if (is_array($value))
            {
                $value = "IN ('" . implode("', '", $value) . "')";
                $param = '';
            }
            else
            {
                $param = '=';
                $value = ' \'' . $value . '\'';
            }
        }
        else
        {
            $value = ' \'' . $value . '\'';
        }

        $columnName = $catalog::parseTableNameForQuery($columnName);
        $this->where[$operation][] = $columnName . ' ' . $param . $value;

        return $this;
    }

    public function whereOr($columnName, $param, $value)
    {
        return $this->where($columnName, $param, $value, 'OR');
    }

    public function whereRaw($where, $operation = 'AND')
    {
        $operation = $operation ? trim(strtoupper($operation)) : 'AND';
        $this->where[] = $operation . $where;
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
            $explode = ', ';
            $orders = implode($explode, $orders);
        }

        return $orders;
    }

    protected function mountWhere($format = false)
    {
        $where = $this->getWhere();

        if (isset($where['AND']))
        {
            $and = $where['AND'];

            if (is_array($and))
            {
                $explode = $format ? " \r\nAND " : ' AND ';
                $where = implode($explode, $and);
            }
        }

        return $where;
    }

    public function getSql($format = false)
    {
        $catalog = $this->getCatalog();

        $tables = $this->getTableName();
        $columns = $this->mountColumns($format);
        $limit = $this->getLimit();
        $offset = $this->getOffset();
        $groupBy = NULL;
        $having = NULL;
        $orderBy = $this->mountOrderBy();
        $where = $this->mountWhere($format);

        return $catalog::mountSelect($tables, $columns, $where, $limit, $offset, $groupBy, $having, $orderBy, NULL, $format);
    }

    public function first()
    {
        $this->setLimit(1);
        $this->setOffset(NULL);

        $sql = $this->getSql();
        $result = $this->getConn()->query($sql);

        if ($result)
        {
            return $result[0];
        }
    }

    public function get()
    {
        $sql = $this->getSql();
        return $this->getConn()->query($sql);
    }

}