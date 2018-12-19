<?php

namespace DataSource;

/**
 * QueryBuilder datasource
 */
class QueryBuilder extends DataSource
{

    /**
     *
     * @var \Db\QueryBuilder
     */
    protected $queryBuilder;

    public function __construct($queryBuilder = NULL)
    {
        $this->setQueryBuilder($queryBuilder);
    }

    public function getQueryBuilder(): \Db\QueryBuilder
    {
        return $this->queryBuilder;
    }

    public function setQueryBuilder(\Db\QueryBuilder $queryBuilder)
    {
        $this->queryBuilder = $queryBuilder;
        return $this;
    }

    public function getQueryBuilderFeeded()
    {
        $qBuilder = $this->getQueryBuilder()
                ->orderBy($this->getOrderBy(), $this->getOrderWay())
                ->limit($this->getLimit(), $this->getOffset())
                ->addWhere($this->getExtraFilter());

        $modelName = $qBuilder->getModelName();

        //workaround to make it work like a default model
        if ($modelName)
        {
            $smartFilter = new \Db\SmartFilter($modelName, $this->getSelectedModelColumns(), $this->getSmartFilter());
            $filters = $smartFilter->createFilters();
            $qBuilder->addWhere($filters);
        }

        return $qBuilder;
    }

    public function executeAggregator(Aggregator $aggregator)
    {

    }

    public function getCount()
    {
        if (is_null($this->count))
        {
            $qBuilder = $this->getQueryBuilderFeeded();
            $qBuilder = clone($qBuilder);
            $this->count = $qBuilder->aggregation('count(*)');
        }

        return $this->count;
    }

    public function getData()
    {
        if ($this->data)
        {
            return $this->data;
        }

        $qBuilder = $this->getQueryBuilderFeeded();

        $this->data = $qBuilder->toCollection();

        return $this->data;
    }

    public function getSelectedModelColumns()
    {
        $qBuilder = $this->getQueryBuilder();
        $modelName = $qBuilder->getModelName();
        $columns = $qBuilder->getColumns();
        $result = array();

        foreach ($columns as $columName)
        {
            $result[$columName] = $modelName::getColumn($columName);
        }

        return $result;
    }

    public function mountColumns($availableColumns = null)
    {
        $qBuilder = $this->getQueryBuilder();
        $modelName = $qBuilder->getModelName();
        $columns = $qBuilder->getColumns();
        $result = array();

        foreach ($columns as $columnName)
        {
            //case it has a model name, vinculate it with the column of model
            if ($modelName)
            {
                $columnModel = $modelName::getColumn($columnName);

                if ($columnModel)
                {
                    $result[$columnName] = \DataSource\Model::createOneColumn($columnModel);
                }
            }
            else
            {
                $result[$columnName] = new \Component\Grid\Column($columnName, $columnName, 'alignLeft');
            }
        }

        $this->setColumns($result);

        return $result;
    }

}
