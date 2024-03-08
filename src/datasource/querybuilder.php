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

    /**
     *
     * @var \Db\Collection
     */
    protected $data;

    /**
     * Make aggregation function trough PHP
     * @var bool
     */
    protected $aggTroughPhp = false;

    public function __construct($queryBuilder = NULL)
    {
        $this->setQueryBuilder($queryBuilder);
    }

    public function getQueryBuilder()
    {
        return $this->queryBuilder;
    }

    public function setQueryBuilder(\Db\QueryBuilder $queryBuilder)
    {
        $this->queryBuilder = $queryBuilder;
        return $this;
    }

    public function isAggTroughPhp(): bool
    {
        return $this->aggTroughPhp;
    }

    public function setAggTroughPhp(bool $aggTroughPhp)
    {
        $this->aggTroughPhp = $aggTroughPhp;
        return $this;
    }

    public function getQueryBuilderFeeded()
    {
        $qBuilder = clone($this->getQueryBuilder());
        $qBuilder->orderBy($this->getOrderBy(), $this->getOrderWay())
                ->limit($this->getLimit(), $this->getOffset())
                ->addWhere($this->getExtraFilter());

        $filters = $this->mountSmartFilters();

        $qBuilder->addWhere($filters);

        return $qBuilder;
    }

    /**
     * Mount the smart function
     *
     * @return array of \Db\Filter
     */
    public function mountSmartFilters()
    {
        $callBack = $this->getSmartFilterCallback();

        if ($callBack)
        {
            return $callBack($this);
        }

        $qBuilder = $this->getQueryBuilder();
        $modelName = $qBuilder->getModelName();

        //workaround to make it work like a default model
        if ($modelName)
        {
            $columns = $this->getSelectedModelColumns();
        }
        else
        {
            $columns = \DataSource\ColumnConvert::gridToDbAll($this->getColumns());
        }

        $smartFilter = new \Db\SmartFilter($modelName,$columns , $this->getSmartFilter());
        return $smartFilter->createFilters();
    }

    protected function getQueryColumnByRealname($realColumnName)
    {
        $qBuilder = $this->getQueryBuilder();
        $columns = $qBuilder->getColumns();

        foreach ($columns as $column)
        {
            $realName = \Db\Column\Column::getRealColumnName($column);

            if ($realColumnName == $realName)
            {
                return $column;
            }
        }

        return null;
    }

    public function executeAggregator(Aggregator $aggregator)
    {
        $data = $this->getData();
        $aggTroughPhp = $this->isAggTroughPhp();

        if (!$aggTroughPhp)
        {
            $aggTroughPhp = $this->getCount() == count($data);
        }

        //we can execute aggregation trough php
        if ($aggTroughPhp && $data instanceof \Db\Collection)
        {
            $result = $data->aggr($aggregator->getMethod(), $aggregator->getColumnName());

            $gridColumn = $this->getColumn($aggregator->getColumnName());

            if ($gridColumn && $gridColumn->getFormatter())
            {
                $formater = $gridColumn->getFormatter();
                $result = $formater->setValue($result)->toHuman();
            }

            return $aggregator->getLabelledValue($result);
        }

        $qBuilder = $this->getQueryBuilderFeeded();
        $realName = $aggregator->getColumnName();
        $sqlColumn = $this->getQueryColumnByRealname($realName);
        $sqlColumn = \Db\Column\Column::getRealSqlColumn($sqlColumn);
        $gridColumn = $this->getColumn($aggregator->getColumnName());

        if ($qBuilder->getGroupBy())
        {
            $method = $aggregator->getMethod();
            //store groupby to apply select without it
            $groupBy = $qBuilder->getGroupBy();
            $query = '(' . $sqlColumn . ')';
            $qBuilder->setGroupBy(null);

            $result = $qBuilder->aggregation($query);
            //restore groupby
            $qBuilder->setGroupBy($groupBy);
        }
        else if (trim(strtolower($aggregator->getMethod()) == Aggregator::METHOD_COUNT_DISTINCT))
        {
            $query = 'count(distinct( ' . $sqlColumn . ' ))';

            $result = $qBuilder->aggregation($query);
        }
        else
        {
            $method = $aggregator->getMethod();
            $query = $method . '( ' . $sqlColumn . ' )';

            if ($gridColumn && $gridColumn->getFormatter() && get_class($gridColumn->getFormatter()) == '\Type\Time')
            {
                $query = 'SEC_TO_TIME( SUM( TIME_TO_SEC( (' . $sqlColumn . ') )))';
            }

            $result = $qBuilder->aggregation($query);
        }

        if ($gridColumn && $gridColumn->getFormatter())
        {
            $formater = $gridColumn->getFormatter();
            $result = $formater->setValue($result)->toHuman();
        }

        return $aggregator->getLabelledValue($result);
    }

    public function getCount()
    {
        if ($this->isAggTroughPhp())
        {
            return $this->getData()->count();
        }

        if (is_null($this->count))
        {
            $qBuilder = $this->getQueryBuilderFeeded();
            $qBuilder = clone($qBuilder);
            $countSql = 'COUNT(*)';

            //TODO verify if it works in all time
            if ($qBuilder->getGroupBy())
            {
                $countSql = 'COUNT(DISTINCT (' . $qBuilder->getGroupBy() . '))';
                $qBuilder->setGroupBy(NULL);
            }

            $this->count = $qBuilder->aggregation($countSql);
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

        $this->adjustColumnAlign();

        return $this->data;
    }

    public function adjustColumnAlign()
    {
        $data = $this->getData();
        $firstItem = $data->first();
        $columns = $this->getColumns();

        foreach ($columns as $idx => $column)
        {
            $value = \DataSource\Grab::getUserValue($column, $firstItem);

            if (\Type\Integer::isNumeric($value) && !$column->getIdentificator())
            {
                $columns[$idx]->setAlign(\Component\Grid\Column::ALIGN_RIGHT);
            }
        }

        $this->setColumns($columns);
    }

    public function getSelectedModelColumns()
    {
        $qBuilder = $this->getQueryBuilder();
        $modelName = $qBuilder->getModelName();
        $columns = $qBuilder->getColumns();
        $result = [];

        foreach ($columns as $columnName)
        {
            $columnName = \Db\Column\Column::getRealColumnName($columnName);
            $column = $modelName::getColumn($columnName);

            if ($column)
            {
                $result[$columnName] = $column;
            }
        }

        return $result;
    }

    public function mountColumns($availableColumns = null)
    {
        $qBuilder = $this->getQueryBuilder();
        $modelName = $qBuilder->getModelName();
        $columns = $qBuilder->getColumns();
        $result = array();

        foreach ($columns as $orignalColumnName)
        {
            //control sql columns with AS
            $columnName = \Db\Column\Column::getRealColumnName($orignalColumnName);
            $columnSql = \Db\Column\Column::getRealSqlColumn($orignalColumnName);
            $columnLabel = self::columnNameToLabel($columnName);

            //jump description columns in grid
            if (\Type\Text::get($columnName)->endsWith('Description'))
            {
                continue;
            }

            $obj = new \Component\Grid\Column($columnName, $columnLabel, 'alignLeft');
            $obj->setSql($columnSql);

            //case it has a model name, vinculate it with the column of model
            if ($modelName)
            {
                $columnModel = $modelName::getColumn($columnName);
                $okay = \DataSource\ColumnConvert::dbToGrid($columnModel);
                $obj = $okay ? $okay : $obj;
            }

            //add support for ..Description column
            /* if (\Type\Text::get($columnName)->endsWith('Description'))
              {
              $originalColumnName = str_replace('Description', '', $columnName);

              if (isset($result[$originalColumnName]))
              {
              $result[$originalColumnName]->setRender(false);
              }

              $columnLabel = str_replace('Description', '', $columnLabel);
              $obj->setLabel($columnLabel);
              } */

            $result[$columnName] = $obj;
        }

        $this->setColumns($result);

        return $result;
    }

    public static function columnNameToLabel($columnName)
    {
        $columnLabel = $columnName;
        //remove "id" in the begin
        if (substr($columnName, 0, strlen('id')) == 'id')
        {
            $columnLabel = str_replace('id', '', $columnName);
        }

        //split by uppercase letter
        $split = preg_split('/(?=[A-Z])/', $columnLabel);
        //implode using space
        return ucfirst(implode(' ', $split));
    }

}
