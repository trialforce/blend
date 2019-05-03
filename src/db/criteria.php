<?php

namespace Db;

/**
 * The field criteria part of sql statment
 */
class Criteria implements \Db\Filter
{

    protected $filters;
    protected $sql;
    protected $sqlParam;
    protected $having;
    protected $args = array();
    protected $type;

    /* Contols if already executed */
    protected $executed = false;

    /**
     * The object called when close
     *
     * @var \Db\QueryBuilder
     */
    protected $closeObject = null;

    /**
     * Construct the criteria
     *
     * @param array $filters array of filter \Db\Cond or \Db\Where
     */
    public function __construct($filters = NULL)
    {
        $this->setFilters($filters);
    }

    public function getType()
    {
        return $this->type;
    }

    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Close group and return the close object
     *
     * @return \Db\QueryBuilder
     */
    public function groupClose()
    {
        return $this->closeObject;
    }

    public function getCloseObject()
    {
        return $this->closeObject;
    }

    /**
     * Set close object, used inside \Db\QueryBuilder
     *
     * @param \Db\QueryBuilder $closeObject
     * @return $this
     */
    public function setCloseObject($closeObject)
    {
        $this->closeObject = $closeObject;
        return $this;
    }

    /**
     * Get the array of filters.
     * Always return an array
     *
     * @return array return an array of filters
     */
    public function getFilters()
    {
        //if is null is clean array
        if (is_null($this->filters))
        {
            return array();
        }

        return is_array($this->filters) ? array_filter($this->filters) : array($this->filters);
    }

    /**
     * Define/Overwrite the condition/where array
     *
     * @param type $filters
     * @return $this
     */
    public function setFilters($filters)
    {
        if ($filters && !is_array($filters))
        {
            $filters = array($filters);
        }

        $this->filters = $filters;
        return $this;
    }

    /**
     * Add a where object, can be \Db\Where or \Db\Cond
     * or any class that implements \Db\Filter interface
     * Or array of this classes.
     *
     * @param mixed $where
     * @return \Db\QueryBuilder
     */
    public function addWhere($where)
    {
        if (is_array($where))
        {
            $this->filters = array_merge($this->getFilters(), $where);
        }
        else
        {
            $this->filters[] = $where;
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
    public function where($columnName, $param, $value = NULL, $condition = 'AND')
    {
        if (is_null($param) && is_null($value))
        {
            $where = new \Db\WhereRaw($columnName);
        }
        else
        {
            //support two parameters
            if (!$value && $value !== '0' && $value !== 0 && $param)
            {
                $value = $param;
                $param = is_array($value) ? 'IN' : '=';
            }

            //$columnName = $catalog::parseTableNameForQuery($columnName);
            //create the condition
            $where = new \Db\Where($columnName, $param, $value, $condition ? $condition : 'AND');
        }

        $this->filters[] = $where;

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
    public function and($columnName, $param, $value = NULL)
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
    public function or($columnName, $param, $value)
    {
        return $this->where($columnName, $param, $value, 'OR');
    }

    public function getSql()
    {
        return $this->sql;
    }

    public function setSql($sql)
    {
        $this->sql = $sql;
        return $this;
    }

    public function getSqlParam()
    {
        return $this->sqlParam;
    }

    public function setSqlParam($sqlParam)
    {
        $this->sqlParam = $sqlParam;
        return $this;
    }

    public function getHaving()
    {
        return $this->having;
    }

    public function setHaving($having)
    {
        if ($having && strlen($having) > 0)
        {
            $this->setType(\Db\Cond::TYPE_HAVING);
            $this->having = $having;
        }

        return $this;
    }

    public function getArgs()
    {
        return $this->args;
    }

    public function setArgs($args)
    {
        $this->args = $args;
        return $this;
    }

    /**
     * Mount WHERE criteria based on an array of filters
     * \Db\Cond or \Db\Where
     *
     * @param array $filters \Db\Cond or \Db\Where
     * @return \stdClass
     * @throws \Exception
     */
    public function execute()
    {
        if ($this->executed)
        {
            return $this;
        }

        $filters = $this->getFilters();
        $args = array();
        $argsHaving = array();
        $sql = '';
        $sqlParam = '';
        $having = '';

        $count = 0;
        $countHaving = 0;

        if (count($filters) > 0)
        {
            foreach ($filters as $filter)
            {
                if (!$filter instanceof \Db\Filter)
                {
                    continue;
                }

                //add suppor for \Db\Criteria inside \Db\Criteria
                //FIXME need full padronization with \Db\Where
                if ($filter instanceof \Db\Criteria)
                {
                    $filter->execute();
                    $myHaving = $filter->getHaving();

                    if ($myHaving)
                    {
                        $having .= $filter->getHaving();
                        $countHaving++;

                        if (!is_null($filtersArgs))
                        {
                            $argsHaving = array_merge($argsHaving, $filter->getArgs());
                        }
                    }
                    else
                    {
                        $sql .= $filter->getString($count === 0);
                        $sqlParam .= $filter->getStringPdo($count === 0);
                        $count++;

                        $filtersArgs = $filter->getArgs();

                        if (!is_null($filtersArgs))
                        {
                            $args = array_merge($args, $filtersArgs);
                        }
                    }
                }
                //FIXME old getType way of having
                else if (strtolower($filter->getType()) == \Db\Cond::TYPE_HAVING)
                {
                    $having .= $filter->getString($countHaving === 0);
                    $countHaving++;

                    if (!is_null($filter->getArgs()))
                    {
                        $argsHaving = array_merge($argsHaving, $filter->getArgs());
                    }
                }
                else
                {
                    $sql .= $filter->getString($count === 0);
                    $sqlParam .= $filter->getStringPdo($count === 0);
                    $count++;

                    $filtersArgs = $filter->getArgs();

                    if (!is_null($filtersArgs))
                    {
                        $args = array_merge($args, $filtersArgs);
                    }
                }
            }
        }

        $argsFinal = array_merge($args, $argsHaving);

        //filters does not import any, it's a new created filters
        $this->setFilters(null);
        $this->setSql($sql);
        $this->setSqlParam($sqlParam); //sql with params (? replaced)
        $this->setHaving($having);
        $this->setArgs($argsFinal);

        //mark as executed
        $this->executed = true;

        return $this;
    }

    /**
     * Static create a Criteria
     *
     * @param array $filters of filters (\Db\Cond or \Db\Where)
     * @return \Db\Criteria
     */
    public static function createCriteria($filters)
    {
        $criteria = new \Db\Criteria($filters);
        return $criteria->execute();
    }

    protected static function cleanSqlString($sqlString)
    {
        return rtrim(rtrim($sqlString, "\r\n"), " ");
    }

    public function getString($first = false)
    {
        $this->execute();
        $sql = $this->getSql();

        if (!$sql)
        {
            return '';
        }

        $operator = $first ? '' : ' AND ';
        return $operator . '(' . self::cleanSqlString($sql) . ') ';
    }

    public function getStringPdo($first = false)
    {
        $this->execute();
        $sql = $this->getSqlParam();

        if (!$sql)
        {
            return '';
        }

        $operator = $first ? '' : ' AND ';
        return $operator . '(' . self::cleanSqlString($sql) . ') ';
    }

}
