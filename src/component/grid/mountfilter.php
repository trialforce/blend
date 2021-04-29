<?php

namespace Component\Grid;

/**
 * Mount an automatic grid filter base on a \Component\Grid\Column and a \Db\Model
 */
class MountFilter
{

    /**
     * Grid column
     * @var \Component\Grid\Column
     */
    private $column;

    /**
     * Model
     * @var \Db\Model
     */
    private $dbModel;

    /**
     * Mount one grid filter
     *
     * @param \Component\Grid\Column $column
     * @param \Db\Model $dbModel
     */
    public function __construct($column, $dbModel)
    {
        $this->column = $column;
        $this->dbModel = $dbModel;
    }

    public function getColumn()
    {
        return $this->column;
    }

    public function getDbModel()
    {
        return $this->dbModel;
    }

    public function setColumn($column)
    {
        $this->column = $column;
        return $this;
    }

    public function setDbModel($dbModel)
    {
        $this->dbModel = $dbModel;
        return $this;
    }

    /**
     * Return an filter based on a grid column and model
     *
     * @return \Component\Grid\filterClass
     */
    public function getFilter()
    {
        $column = $this->column;
        $column instanceof \Component\Grid\Column;

        if (!$column)
        {
            return NULL;
        }

        //avoid columns that end with description
        if (strpos($column->getName(), 'Description') > 0)
        {
            return null;
        }

        $filter = NULL;
        $dbModel = $this->dbModel;
        $dataType = $column->getType();
        //$filterType = $column->getFilterType();
        $dbColumn = null;

        //don't mount filter if column don't has data type, or if don't have to be filtered
        if (!$dataType)
        {
            return NULL;
        }

        //try to get column from database/model
        if ($dbModel instanceof \Db\Model)
        {
            $realColumnName = \Db\Column\Column::getRealColumnName($column->getName());
            $dbColumn = $dbModel::getColumn($realColumnName);
        }

        //verify if is needed to mount the filter by database/model column
        if ($dbColumn instanceof \Db\Column\Column)
        {
            $filterClassName = $dbColumn->getFilterClassName();
            $filter = new $filterClassName($column);

            if (method_exists($filter, 'setDbColumn'))
            {
                $filter->setDbColumn($dbColumn);
            }
        }

        //if not find in model, create a default filter based on column type it's the default fallback
        if (!$filter)
        {
            $filterClass = \Component\Grid\MountFilter::getFilterClass($column);

            $filter = new $filterClass($column, NULL);
        }

        $filter instanceof \Filter\Text;
        //active filter
        $filter->setFilterType(1);
        $filter->setFilterLabel($column->getLabel());

        return $filter;
    }

    public static function getFilterClass(\Component\Grid\Column $column)
    {
        $dataType = $column->getType() == 'bool' ? 'boolean' : $column->getType();
        $formatter = $column->getFormatter();

        if ($formatter instanceof \Type\DateTime)
        {
            $dataType = 'datetime';
        }
        else if ($formatter instanceof \Db\ConstantValues)
        {
            $dataType = 'reference';
        }

        $filterClass = '\\Filter\\' . ucfirst($dataType);

        return $filterClass;
    }

    /**
     * Static method to construct an array of filters
     *
     * @param array $columns
     * @param \Dd\Model $dbModel
     * @return array
     */
    public static function getFilters($columns, $dbModel, $fixedFilters = null)
    {
        if (!is_array($columns))
        {
            return NULL;
        }

        $filters = $fixedFilters;
        $extraFilters = array();

        if (is_array($fixedFilters))
        {
            foreach ($fixedFilters as $filter)
            {
                if ($filter instanceof \Filter\Text)
                {
                    $extraFilters[] = $filter->getFilterName();
                }
            }
        }

        //prepare filters to an array
        foreach ($columns as $column)
        {
            $mountFilter = new \Component\Grid\MountFilter($column, $dbModel);
            $filter = $mountFilter->getFilter();

            //avoid create two filters for the same column
            if (is_array($filter))
            {
                foreach ($filter as $filt)
                {
                    if (!in_array($filt->getFilterName(), $extraFilters))
                    {
                        $filters[$filt->getFilterName()] = $filt;
                    }
                }
            }
            else if ($filter)
            {
                if (!in_array($filter->getFilterName(), $extraFilters))
                {
                    $filters[$filter->getFilterName()] = $filter;
                }
            }
        }

        foreach ($filters as $filter)
        {
            if (!$filter->getFilterGroup())
            {
                $filter->setFilterGroup(\Filter\Text::GROUP_MAIN);
            }
        }

        if ($dbModel)
        {
            $filters = array_merge($filters, self::mountFiltersRelations($dbModel));
        }

        return $filters;
    }

    /**
     * Mount filter for multiple relations
     *
     * @param \Db\Model $dbModel original model
     * @return array array of filters
     */
    public static function mountFiltersRelations(\Db\Model $dbModel)
    {
        $filters = array();

        $relations = $dbModel::getRelations();

        if (isCountable($relations))
        {
            foreach ($relations as $relation)
            {
                $filters = array_merge($filters, self::mountFiltersRelation($dbModel, $relation));
            }
        }

        return $filters;
    }

    /**
     * Mount filters from one relation
     *
     * @param \Db\Model $dbModel original model
     * @param \Db\Relation $relation the relation
     * @return \Filter\Text the filter
     */
    public static function mountFiltersRelation(\Db\Model $dbModel, \Db\Relation $relation)
    {
        $otherModel = $relation->getModelName();
        $columns = $otherModel::getColumns();
        $label = $relation->getLabel() ? $relation->getLabel() : $otherModel::getLabel();
        $tableName = $otherModel::getTableName();
        $labelAscii = \Type\Text::get($label)->toFile("-");
        $filters = [];

        foreach ($columns as $column)
        {
            $column instanceof \Db\Column\Column;
            $filterClassName = $column->getFilterClassName();
            $filterName = $labelAscii . '-' . $tableName . '-' . $column->getName();
            $filterSql = '(SELECT ' . $column->getName() . ' FROM ' . $tableName . ' WHERE ' . $relation->getSql() . ')';

            $filter = new $filterClassName();
            $filter instanceof \Filter\Text;

            //hide the model label, so we can filter in JS
            $filter->setFilterLabel($column->getLabel());
            $filter->setFilterSql($filterSql);
            $filter->setFilterName($filterName);
            $filter->setFilterType(\Filter\Text::FILTER_TYPE_ENABLE);
            $filter->setFilterGroup($label);

            if (method_exists($filter, 'setDbColumn'))
            {
                $filter->setDbColumn($column);
            }

            $filters[$filterName] = $filter;
        }

        return $filters;
    }

    /**
     * Diable a list of filter groups
     * @param array $filters the filter array
     * @param array $groupLabels the array of filter group labels
     * @return array the filtered filter list
     */
    public static function disableGroup(array $filters, array $groupLabels)
    {
        foreach ($filters as $position => $filter)
        {
            $filter instanceof \Filter\Text;

            foreach ($groupLabels as $groupLabel)
            {
                if (strtoupper($filter->getFilterGroup()) == strtoupper($groupLabel))
                {
                    $filter->setFilterType(\Filter\Text::FILTER_TYPE_DISABLE);
                    unset($filters[$position]);
                }
            }
        }

        return $filters;
    }

}
