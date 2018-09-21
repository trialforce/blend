<?php

namespace DataSource;

/**
 * Datasource de modelo
 */
class ModelGroup extends DataSource
{

    /**
     *
     * @var \Db\Model
     */
    protected $model;

    /**
     * Cached data
     *
     * @var array
     */
    protected $data;

    /**
     * Db Columns
     * @var Array
     */
    protected $originalColumns;

    public function __construct(\Db\Model $model)
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
            $data = $this->getData();

            $this->count = count($data);
        }

        return $this->count;
    }

    public function setColumns($columns)
    {
        if (!$this->originalColumns)
        {
            $this->originalColumns = $columns;
        }

        return parent::setColumns($columns);
    }

    public function getOriginalColumns()
    {
        return $this->originalColumns;
    }

    public function setOriginalColumns(Array $originalColumns)
    {
        $this->originalColumns = $originalColumns;
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
            $filters = $this->getExtraFilter();

            $this->data = $model::search($this->originalColumns, $filters, NULL, NULL, $this->getOrderBy(), $this->getOrderWay(), 'stdClass');
        }

        return $this->data;
    }

    public function setData($data)
    {
        $this->data = $data;
        return $this;
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

        $query = $aggregator->getMethod() . '( ' . $sqlColumn . ' )';
        $filters = $model->smartFilters($this->getSmartFilter(), $this->getExtraFilter());

        return $aggregator->getLabelledValue($model->aggregation($filters, $query, $forceExternalSelect));
    }

    /**
     * Create grid columns based on model information
     *
     * @return array
     */
    public function mountColumns()
    {
        return \DataSource\Model::createColumn($this->getColumns(), $this->getOrderBy());
    }

}
