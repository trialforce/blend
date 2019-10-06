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

    public function getQueryBuilderFeeded()
    {
        $qBuilder = clone($this->getQueryBuilder());
        $qBuilder instanceof \Db\QueryBuilder;
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
            $smartFilter = new \Db\SmartFilter($modelName, $this->getSelectedModelColumns(), $this->getSmartFilter());
            return $smartFilter->createFilters();
        }

        return null;
    }

    protected function getQueryColumnByRealname($realColumnName)
    {
        $qBuilder = $this->getQueryBuilder();
        $columns = $qBuilder->getColumns();

        foreach ($columns as $column)
        {
            $realName = \Db\Column::getRealColumnName($column);

            if ($realColumnName == $realName)
            {
                return $column;
            }
        }
    }

    public function executeAggregator(Aggregator $aggregator)
    {
        $qBuilder = $this->getQueryBuilderFeeded();
        $realName = $aggregator->getColumnName();
        $sqlColumn = $this->getQueryColumnByRealname($realName);
        $sqlColumn = \Db\Column::getRealSqlColumn($sqlColumn);

        $method = $aggregator->getMethod();
        $query = $method . '( ' . $sqlColumn . ' )';

        $result = $qBuilder->aggregation($query);

        return $aggregator->getLabelledValue($result);
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

            if (\Type\Integer::isNumeric($value))
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
        $result = array();

        foreach ($columns as $columnName)
        {
            $columnName = \Db\Column::getRealColumnName($columnName);
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
            $columnName = \Db\Column::getRealColumnName($orignalColumnName);
            $columnSql = \Db\Column::getRealSqlColumn($orignalColumnName);
            $columnLabel = self::columnNameToLabel($columnName);

            $obj = new \Component\Grid\Column($columnName, $columnLabel, 'alignLeft');
            $obj->setFilter(TRUE)->setSql($columnSql);

            //case it has a model name, vinculate it with the column of model
            if ($modelName)
            {
                $columnModel = $modelName::getColumn($columnName);
                $columnModel instanceof \Db\Column;

                if ($columnModel)
                {
                    $obj = \DataSource\Model::createOneColumn($columnModel);
                }
            }

            //add support for ..Description column
            if (\Type\Text::get($columnName)->endsWith('Description'))
            {
                $originalColumnName = str_replace('Description', '', $columnName);

                if (isset($result[$originalColumnName]))
                {
                    $result[$originalColumnName]->setRender(false);
                }

                $columnLabel = str_replace('Description', '', $columnLabel);
                $obj->setLabel($columnLabel);
            }

            //if (\Type\Integer::isNumeric($value))

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
