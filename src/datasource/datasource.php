<?php

namespace DataSource;

/**
 *
 * DataSource is a interface for grid to access data.
 * It control pagination, query search, aggregations and counters
 *
 */
abstract class DataSource
{

    /**
     * Default page limit
     */
    const DEFAULT_PAGE_LIMIT = 15;

    /**
     * Search limit
     *
     * @var int
     */
    protected $limit;

    /**
     * Offset
     *
     * @var int
     */
    protected $offset;

    /**
     * Order by
     * @var string
     */
    protected $orderBy;

    /**
     * Order way
     * @var string
     */
    protected $orderWay;

    /**
     * Smart filter
     * @var string
     */
    protected $smartFilter;

    /**
     * Extra filter
     * @var array
     */
    protected $extraFilter;

    /**
     * Count
     * @var int
     */
    protected $count;

    /**
     * Columns list
     * @var array
     */
    protected $columns;

    /**
     * Page/Dom
     * @var \View\Dom
     */
    protected $page;

    /**
     * Limit for page
     * @var int
     */
    protected $paginationLimit = self::DEFAULT_PAGE_LIMIT;

    /**
     * Cached data
     *
     * @var array
     */
    protected $data;

    /**
     * Array of \Datasource\Aggregator
     *
     * @var array
     */
    protected $aggregator;

    /**
     * A smart filter callback function
     *
     * @var callable
     */
    protected $smartFilterCallback;

    public function getLimit()
    {
        return $this->limit;
    }

    public function setLimit($limit)
    {
        $this->limit = $limit;
        return $this;
    }

    public function getOffset()
    {
        return $this->offset;
    }

    public function setOffset($offset)
    {
        $this->offset = $offset;

        return $this;
    }

    public function getPaginationLimit()
    {
        return $this->paginationLimit;
    }

    public function setPaginationLimit($paginationLimit)
    {
        $this->setOffset($this->getPage() * $paginationLimit);
        $this->paginationLimit = $paginationLimit;
        return $this;
    }

    public function setPage($page)
    {
        $this->setOffset($page * $this->getPaginationLimit());
        $this->setLimit($this->getPaginationLimit());
        $this->page = $page;

        return $this;
    }

    public function getPage()
    {
        return $this->page;
    }

    public function getOrderBy()
    {
        return $this->orderBy;
    }

    public function getOrderByParsedForColumn($columName, $passedOrderBy = null)
    {
        $orderBy = $passedOrderBy ? $passedOrderBy : $this->orderBy;
        $orders = $this->getOrderByParsed($orderBy);

        if (isset($orders[$columName]))
        {
            return $orders[$columName];
        }

        $obj = new \stdClass();
        $obj->orderBy = null;
        $obj->orderWay = null;

        return $obj;
    }

    public function getOrderByParsed($orderBy)
    {
        $result = [];

        if (trim($orderBy . '') == '')
        {
            return $result;
        }

        $explode = explode(',', trim($orderBy));

        foreach ($explode as $line)
        {
            $orderWay = '';
            $lineExplode = explode(' ', $line);
            $lastWord = strtolower($lineExplode[count($lineExplode) - 1]);

            if ($lastWord == 'desc' || $lastWord == 'asc')
            {
                unset($lineExplode[count($lineExplode) - 1]);
                $orderWay = $lastWord;
            }

            $obj = new \stdClass();
            $obj->orderBy = trim(implode(' ', $lineExplode));
            $obj->orderWay = $orderWay;

            $result[$obj->orderBy] = $obj;
        }

        return $result;
    }

    public function setOrderBy($orderBy)
    {
        $this->orderBy = $orderBy;

        return $this;
    }

    public function getOrderWay()
    {
        return $this->orderWay;
    }

    public function setOrderWay($orderWay)
    {
        $this->orderWay = $orderWay;

        return $this;
    }

    public function getSmartFilter()
    {
        return $this->smartFilter;
    }

    public function setSmartFilter($smartFilter)
    {
        $this->smartFilter = $smartFilter;
        return $this;
    }

    public function getExtraFilter()
    {
        $extraFilter = $this->extraFilter;

        if (!is_array($extraFilter))
        {
            $extraFilter = array($extraFilter);
        }

        return $extraFilter;
    }

    public function setExtraFilter($extraFilter)
    {
        $this->extraFilter = $extraFilter;
        return $this;
    }

    /**
     * Add a filter to search query
     *
     * @param Mixed $filter
     * @return \DataSource\DataSource
     */
    public function addExtraFilter($filter)
    {
        if (!$filter)
        {
            return $this;
        }

        if (is_array($filter))
        {
            foreach ($filter as $filt)
            {
                $this->extraFilter[] = $filt;
            }
        }
        else
        {
            $this->extraFilter[] = $filter;
        }

        return $this;
    }

    /**
     * Add some extra filters
     *
     * @param array $filters
     * @return \DataSource\DataSource
     */
    public function addExtraFilters($filters)
    {
        if (is_array($filters))
        {
            foreach ($filters as $filter)
            {
                if ($filter && is_object($filter))
                {
                    $this->addExtraFilter($filter);
                }
            }
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
     * @return \DataSource\DataSource
     */
    public function where($columnName, $param = NULL, $value = NULL, $condition = 'AND')
    {
        $filter = new \Db\Where($columnName, $param, $value, $condition ? $condition : 'AND');
        $this->addExtraFilter($filter);

        return $this;
    }

    /**
     * Add a where condition to the were list, but only if a value is passed
     *
     * @param string $columnName the column name
     * @param string $param the condition param =, IN , >= etc
     * @param string $value the filtered value
     * @param string $condition AND, OR, etc
     * @return \DataSource\DataSource
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
     * Add an "AND" condition
     *
     * @param string $columnName column name
     * @param string $param the condicition param (=, in, >=)
     * @param string $value the filter value
     * @return \DataSource\DataSource
     */
    public function and($columnName, $param = NULL, $value = NULL, $condition = 'AND')
    {
        return $this->where($columnName, $param, $value, $condition);
    }

    public function andIf($columnName, $param = NULL, $value = NULL, $condition = 'AND')
    {
        return $this->where($columnName, $param, $value, $condition);
    }

    /**
     * Return the list of aggregators
     *
     * @return array of \DataSource\Aggregator
     */
    public function getAggregator()
    {
        return $this->aggregator;
    }

    /**
     * Define a list of aggregators
     *
     * @param type $aggregator
     * @return \DataSource\DataSource
     */
    public function setAggregator($aggregator)
    {
        if ($aggregator instanceof Aggregator)
        {
            $this->addAggregator($aggregator);
        }
        else
        {
            $this->aggregator = $aggregator;
        }

        return $this;
    }

    /**
     * Add aggregator method
     *
     * @param Aggregator $aggregator
     */
    public function addAggregator($aggregator)
    {
        if (is_array($aggregator))
        {
            foreach ($aggregator as $agg)
            {
                $this->addAggregator($agg);
            }
        }
        else
        {
            $this->aggregator[$aggregator->getColumnName()] = $aggregator;
        }
    }

    public function getSmartFilterCallback()
    {
        return $this->smartFilterCallback;
    }

    public function setSmartFilterCallback($smartFilterCallback)
    {
        $this->smartFilterCallback = $smartFilterCallback;
        return $this;
    }

    /**
     *
     * @return type
     */
    public function getCount()
    {
        return $this->count;
    }

    public function setCount($count)
    {
        $this->count = $count;
    }

    public function getColumns()
    {
        if (!$this->columns)
        {
            $this->columns = $this->mountColumns();
        }

        return $this->columns;
    }

    /**
     * Return some column based on his name
     *
     * @param string $columName column name
     * @return \Component\Grid\Column the grid column object
     */
    public function getColumn($columName)
    {
        $columns = $this->getColumns();

        if (isset($columns[$columName]))
        {
            return $columns[$columName];
        }
    }

    /**
     * Add a collumn to datasource
     *
     * @param \Component\Grid\Column $column
     * @return $this
     */
    public function addColumn(\Component\Grid\Column $column = null)
    {
        if (!$column)
        {
            return $this;
        }

        $columns = $this->columns;
        $columns[$column->getName()] = $column;
        $this->setColumns($columns);

        return $this;
    }

    public function setColumns($columns)
    {
        $this->columns = $columns;

        return $this;
    }

    /**
     * Remove an unnecessary column
     *
     * @param string $name name of colum to remove
     */
    public function removeColumn($name)
    {
        $columns = $this->getColumns();

        foreach ($columns as $index => $column)
        {
            $column->setGrid($this);

            if ($column->getName() == $name)
            {
                unset($this->columns[$index]);
                break;
            }
        }
    }

    /**
     * Default column creation based on data
     */
    public function mountColumns($availableColumns = null)
    {
        $columns = [];
        $data = $this->getData();

        if (!$data || !isIterable($data))
        {
            return null;
        }

        $data = array_values($data);

        if (isset($data[0]))
        {
            $item = $data[0];

            $columns = \DataSource\ColumnConvert::toGridItemAll($item);
        }

        $this->setColumns($columns);

        return $columns;
    }

    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }

    /**
     * Execute aggregation function
     */
    public abstract function executeAggregator(\DataSource\Aggregator $aggregator);

    /**
     * Get the data from datasource, executes the search on "database"
     */
    public abstract function getData();

}
