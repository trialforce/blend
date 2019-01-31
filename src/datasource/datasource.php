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
        $this->extraFilter[] = $filter;

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
        $this->aggregator[$aggregator->getColumnName()] = $aggregator;
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
        $data = $this->getData();

        if (!$data)
        {
            return null;
        }

        $data = array_values($data);

        if (isset($data[0]))
        {
            $item = $data[0];

            if ($item instanceof \Db\Model)
            {
                return \DataSource\Model::createColumn($item->getColumns());
            }
            else if (is_object($item))
            {
                //convert to array to use above
                $item = (array) $item;
            }

            if (is_array($item))
            {
                foreach ($item as $name => $value)
                {
                    //support for private variables
                    $name = str_replace(' * ', '', $name);
                    $align = \Component\Grid\Column::ALIGN_LEFT;

                    //if is numeric align to right
                    if (\Type\Integer::isNumeric($value . ''))
                    {
                        $align = \Component\Grid\Column::ALIGN_RIGHT;
                    }

                    $columns[$name] = new \Component\Grid\Column($name, $name, $align);
                }

                $this->setColumns($columns);

                return $columns;
            }
        }

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
     * Obtem os dados do datasource, executa a pesquisa
     */
    public abstract function getData();
}
