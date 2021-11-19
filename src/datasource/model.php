<?php

namespace DataSource;

/**
 * \Db\Model DataSource.
 * A \Datasource that uses \Db\Model as it's source
 */
class Model extends DataSource
{

    /**
     *
     * @var \Db\Model
     */
    protected $model;

    /**
     * Use only datasouce columns for search
     * @var bool
     */
    protected $useColumnsForSearch = false;

    public function __construct($model = NULL)
    {
        $this->setModel($model);
    }

    /**
     * Return the \DB\Model
     *
     * @return \Db\Model
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * Define the model
     *
     * @param \Db\Model $model
     */
    public function setModel($model)
    {
        $this->model = $model;
    }

    /**
     * Return if is to use all columns or only DataSource coluns in search
     *
     * @return bool
     */
    public function getUseColumnsForSearch()
    {
        return $this->useColumnsForSearch ? true : false;
    }

    /**
     * Define ifs is to use only DataSource columns or all columns, for search
     *
     * @param bool $useColumnsForSearch
     * @return $this
     */
    public function setUseColumnsForSearch($useColumnsForSearch)
    {
        $this->useColumnsForSearch = $useColumnsForSearch;
        return $this;
    }

    /**
     * Count the total data without limits and offsets
     *
     * @return int
     */
    public function getCount()
    {
        if (is_null($this->count))
        {
            $model = $this->model;

            //programatelly callback
            if ($this->getSmartFilterCallback())
            {
                $filters = $this->mountCallBackFilters();
            }
            else
            {
                $columns = $this->getUseColumnsForSearch() ? $this->getDbColumns() : $model->getColumns();
                $columnsFilter = $this->getUseColumnsForSearch() ? $columns : $this->filterOnlySmartSearchableColumns($columns);
                $filters = $model->smartFilters($this->getSmartFilter(), $this->getExtraFilter(), $columnsFilter);
            }

            $this->count = $model->count($filters);
        }

        return $this->count;
    }

    protected function mountCallBackFilters()
    {
        $filters = null;
        $callBack = $this->getSmartFilterCallback();

        //programatelly callback
        if ($callBack)
        {
            $filtersCallBack = $callBack($this);
            $filters = array_merge(is_array($filtersCallBack) ? $filtersCallBack : array(), $this->getExtraFilter());
        }

        return $filters;
    }

    /**
     * Return the datasource data, execute sql if needed
     *
     * @return array
     */
    public function getData()
    {
        if (is_null($this->data) || (isIterable($this->data) && count($this->data) == 0))
        {
            $model = $this->model;
            //programatelly callback
            if ($this->getSmartFilterCallback())
            {
                $this->data = $model->find($this->mountCallBackFilters(), $this->getLimit(), $this->getOffset(), $this->getOrderBy(), $this->getOrderWay());
            }
            else
            {
                $columns = $this->getUseColumnsForSearch() ? $this->getDbColumns() : $model->getColumns();

                $columnsFilter = $this->getUseColumnsForSearch() ? $columns : $this->filterOnlySmartSearchableColumns($columns);
                $filters = $model->smartFilters($this->getSmartFilter(), $this->getExtraFilter(), $columnsFilter);
                $this->data = $model->search($columns, $filters, $this->getLimit(), $this->getOffset(), $this->getOrderBy(), $this->getOrderWay());
            }
        }

        return $this->data;
    }

    private function filterOnlySmartSearchableColumns($columns)
    {
        $dsColumns = $this->getColumns();
        $result = array();

        foreach ($columns as $column)
        {
            $column instanceof \Db\Column\Column;
            $dsColumn = isset($dsColumns[$column->getName()]) ? $dsColumns[$column->getName()] : null;

            if ($dsColumn instanceof \Component\Grid\Column && $dsColumn->getSmartFilter())
            {
                $result[$column->getName()] = $column;
            }
        }

        return $result;
    }

    /**
     * Return the list of \Db\Column\Column that represents the datasource columns
     *
     * @return array of \Db\Column\Column
     */
    public function getDbColumns()
    {
        $dsColumns = $this->getColumns();
        $model = $this->getModel();
        $result = array();

        foreach ($dsColumns as $dsColumn)
        {
            $dsColumn instanceof \Component\Grid\Column;
            $dbColumn = $model->getColumn($dsColumn->getName());

            if ($dbColumn instanceof \Db\Column\Column)
            {
                $result[$dbColumn->getName()] = $dbColumn;
            }
        }

        return $result;
    }

    /**
     * Execute multiple aggregators in one time
     *
     * @param Array $aggregators
     * @return array
     */
    public function executeAggregators($aggregators)
    {
        $model = $this->model;
        $connInfoType = $model->getConnInfo()->getType();
        $querys = NULL;

        foreach ($aggregators as $agg)
        {
            $method = $agg->getMethod();
            $column = $model->getColumn($agg->getColumnName());
            $sqlColumn = $agg->getColumnName();

            if (!$column)
            {
                continue;
            }

            $referenceSql = $column->getSql(FALSE);
            $subquery = $method . '( ' . $referenceSql[0] . ' )';

            if (!$column)
            {
                throw new \UserException('Column ' . $sqlColumn . ' não encontrada na agregação de dados!');
            }

            if ($method == Aggregator::METHOD_SUM && $column->getType() == \Db\Column\Column::TYPE_TIME && $connInfoType == \Db\ConnInfo::TYPE_MYSQL)
            {
                $subquery = 'SEC_TO_TIME( SUM( TIME_TO_SEC( (' . $referenceSql[0] . ') )))';
            }

            $querys[$sqlColumn] = $subquery;
        }

        $parseResult = NULL;

        if (!empty($querys))
        {
            //programatelly callback
            $filters = NULL;
            if ($this->getSmartFilterCallback())
            {
                $filters = $this->mountCallBackFilters();
            }
            else
            {
                $columns = $this->getUseColumnsForSearch() ? $this->getDbColumns() : $model->getColumns();
                $columnsFilter = $this->getUseColumnsForSearch() ? $columns : $this->filterOnlySmartSearchableColumns($columns);
                $filters = $model->smartFilters($this->getSmartFilter(), $this->getExtraFilter(), $columnsFilter);
            }

            $result = $model->aggregations($filters, $querys);

            if ($result)
            {
                foreach ($aggregators as $agg)
                {
                    $method = $agg->getMethod();
                    $column = $model->getColumn($agg->getColumnName());
                    $propertyName = 'aggregation' . $agg->getColumnName();
                    $value = $result->$propertyName;

                    if ($method == Aggregator::METHOD_SUM && $column->getType() == \Db\Column\Column::TYPE_TIME)
                    {
                        $value = \Type\Time::get($value)->toHuman();
                    }

                    $parseResult['aggregation' . $agg->getColumnName()] = $agg->getLabelledValue($value);
                }
            }
        }

        return $parseResult;
    }

    /**
     * Execute aggregator
     *
     * @param \DataSource\Aggregator $aggregator
     *
     * @return mixed
     */
    public function executeAggregator(Aggregator $aggregator)
    {
        $result = array_values($this->executeAggregators([$aggregator]));
        return $result[0];
    }

    /**
     * Create grid columns based on model information
     *
     * @return array
     */
    public function mountColumns($availableColumns = null)
    {
        return \DataSource\ColumnConvert::dbToGridAll($this->model);
    }

}
