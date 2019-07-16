<?php

namespace DataSource;

/**
 * Datasource de modelo
 */
class Model extends DataSource implements \Disk\JsonAvoidPropertySerialize
{

    /**
     *
     * @var \Db\Model
     */
    protected $model;

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
                $filters = $model->smartFilters($this->getSmartFilter(), $this->getExtraFilter());
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
                $this->data = $model->smartFind($this->getSmartFilter(), $this->getExtraFilter(), $this->getLimit(), $this->getOffset(), $this->getOrderBy(), $this->getOrderWay());
            }
        }

        return $this->data;
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
        $forceExternalSelect = FALSE;
        $querys = NULL;

        foreach ($aggregators as $agg)
        {
            $method = $agg->getMethod();
            $sqlColumn = $agg->getColumnName();
            $column = $model->getColumn($agg->getColumnName());

            if (!$column)
            {
                continue;
            }

            if ($column instanceof \Db\SearchColumn)
            {
                $forceExternalSelect = TRUE;
            }

            $subquery = $method . '( ' . $sqlColumn . ' )';

            if (!$column)
            {
                throw new \UserException('Column ' . $sqlColumn . ' não encontrada na agregação de dados!');
            }

            if ($method == Aggregator::METHOD_SUM && $column->getType() == \Db\Column::TYPE_TIME && $connInfoType == \Db\ConnInfo::TYPE_MYSQL)
            {
                $subquery = 'SEC_TO_TIME( SUM( TIME_TO_SEC( (' . $sqlColumn . ') )))';
            }

            $querys[$sqlColumn] = $subquery;
        }

        $parseResult = NULL;

        if (!empty($querys))
        {
            $filters = $model->smartFilters($this->getSmartFilter(), $this->getExtraFilter());
            $result = $model->aggregations($filters, $querys, $forceExternalSelect);

            if ($result)
            {
                foreach ($aggregators as $agg)
                {
                    $method = $agg->getMethod();
                    $column = $model->getColumn($agg->getColumnName());
                    $propertyName = 'aggregation' . $agg->getColumnName();
                    $value = $result->$propertyName;

                    if ($method == Aggregator::METHOD_SUM && $column->getType() == \Db\Column::TYPE_TIME)
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
        $model = $this->model;
        $sqlColumn = $aggregator->getColumnName();
        $column = $model->getColumn($aggregator->getColumnName());
        $forceExternalSelect = FALSE;

        if ($column instanceof \Db\SearchColumn)
        {
            $forceExternalSelect = TRUE;
        }

        $connInfoType = $model->getConnInfo()->getType();

        $method = $aggregator->getMethod();
        $query = $method . '( ' . $sqlColumn . ' )';

        //make sum of time work
        if ($method == Aggregator::METHOD_SUM && $column->getType() == \Db\Column::TYPE_TIME && $connInfoType == \Db\ConnInfo::TYPE_MYSQL)
        {
            $query = 'SEC_TO_TIME( SUM( TIME_TO_SEC( (' . $sqlColumn . ') )))';
        }

        $filters = $model->smartFilters($this->getSmartFilter(), $this->getExtraFilter());
        $result = $model->aggregation($filters, $query, $forceExternalSelect);

        if ($method == Aggregator::METHOD_SUM && $column->getType() == \Db\Column::TYPE_TIME)
        {
            $result = \Type\Time::get($result)->toHuman();
        }

        return $aggregator->getLabelledValue($result);
    }

    /**
     * Create grid columns based on model information
     *
     * @return array
     */
    public function mountColumns($availableColumns = null)
    {
        return \DataSource\Model::createColumn($this->model->getColumns(), $this->getOrderBy());
    }

    /**
     * Create grid columns based on model information
     *
     * @param array $columns
     * @param string $orderBy
     * @return \Component\Grid\Column
     */
    public static function createColumn($columns, $orderBy = NULL)
    {
        //avoid errors in PHPMD nullifyng the parameter
        //TODO avaliate why this is here
        $orderBy = null;
        $gridColumns = array();

        //default checkcolumn
        //$gridColumns[] = new \Component\Grid\CheckColumn( null, 'check' );
        if (is_array($columns))
        {
            foreach ($columns as $column)
            {
                $gridColumns[$column->getName()] = self::createOneColumn($column);
            }
        }

        return $gridColumns;
    }

    /**
     * Create one column
     *
     * @param \Db\SearchColumn $column
     * @return \Component\Grid\Column
     */
    public static function createOneColumn($column)
    {
        $columnLabel = $column->getLabel() ? $column->getLabel() : $column->getName();
        $columnLabel = $columnLabel == 'Código' ? 'Cód' : $columnLabel;

        if ($column->getType() == \Db\Column::TYPE_TIMESTAMP || $column->getType() == \Db\Column::TYPE_DATETIME || $column->getType() == \Db\Column::TYPE_DATE)
        {
            $gridColumn = new \Component\Grid\Column($column->getName(), $columnLabel, \Component\Grid\Column::ALIGN_RIGHT, $column->getType());
        }
        else if ($column->getType() == \Db\Column::TYPE_BOOL || $column->getType() == \Db\Column::TYPE_TINYINT)
        {
            $gridColumn = new \Component\Grid\BoolColumn($column->getName(), $columnLabel, \Component\Grid\Column::ALIGN_RIGHT, $column->getType());
        }
        else if ($column->isPrimaryKey())
        {
            $gridColumn = new \Component\Grid\PkColumnEdit($column->getName(), $columnLabel, \Component\Grid\Column::ALIGN_COLAPSE, $column->getType());
        }
        else
        {
            $gridColumn = new \Component\Grid\Column($column->getName(), $columnLabel, \Component\Grid\Column::ALIGN_LEFT, $column->getType());

            if (($column->getType() == \Db\Column::TYPE_INTEGER || $column->getType() == \Db\Column::TYPE_DECIMAL || $column->getType() == \Db\Column::TYPE_TIME) && !$column->getReferenceDescription())
            {
                $gridColumn->setAlign(\Component\Grid\Column::ALIGN_RIGHT);
            }
        }

        $gridColumn->setIdentificator($column->isPrimaryKey());

        //search column has no filter as default
        if ($column instanceof \Db\SearchColumn)
        {
            $sqls = $column->getSql(FALSE);

            if (isset($sqls[0]))
            {
                $gridColumn->setSql($sqls[0]);
            }

            $gridColumn->setFilter(FALSE);
        }

        //hide text columns by default
        if ($column->getType() === \Db\Column::TYPE_TEXT)
        {
            $gridColumn->setRender(FALSE);
        }

        return $gridColumn;
    }

    public function listAvoidPropertySerialize()
    {
        $avoid[] = 'data';
        $avoid[] = 'count';
        $avoid[] = 'page';
        $avoid[] = 'paginationLimit';

        return $avoid;
    }

}
