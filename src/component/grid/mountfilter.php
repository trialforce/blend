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
        $filterType = $column->getFilterType();

        //don't mount filter if column don't has data type, or if don't have to be filtered
        if (!$dataType || !$filterType)
        {
            return NULL;
        }

        //try to get column from database/model
        if ($dbModel instanceof \Db\Model)
        {
            $dbColumn = $dbModel::getColumn($column->getSplitName());
        }

        //verify if is needed to mount the filter by database/model column
        if ($dbColumn instanceof \Db\Column)
        {
            $filter = $this->mountDbColumnFilter($dbColumn, $column);
        }

        //if not find in model, create a default filter based on column type
        if (!$filter)
        {
            $dataType = $dataType == 'bool' ? 'boolean' : $dataType;
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
            $filter = new $filterClass($column, NULL, $filterType);
        }

        return $filter;
    }

    /**
     * Mount a filter based on a \Db\Column
     *
     * @param \Db\Column $dbColumn
     * @param \Component\Grid\Column $column
     * @return \Filter\Reference
     */
    public function mountDbColumnFilter(\Db\Column $dbColumn, Column $column)
    {
        $filter = NULL;

        if ($dbColumn->getReferenceTable() || $dbColumn->getConstantValues())
        {
            //não faz dbcolumn com classes diferentes
            if ($dbColumn->getClass())
            {
                $filter = new \Filter\Integer($column, NULL, $column->getFilterType());
            }
            else
            {
                $filter = new \Filter\Reference($column, $dbColumn, $column->getFilterType());
            }
        }

        return $filter;
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
        $filters = array();

        if (!is_array($columns) || !$dbModel)
        {
            return NULL;
        }

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

        if (is_array($filters))
        {
            //call order in filters
            usort($filters, 'self::filterSort');
        }

        return $filters;
    }

    /**
     * Organize the filters to put it ordened
     * VERY SLOW
     *
     * @param type $first
     * @param type $second
     * @return int
     */
    public static function filterSort($first, $second)
    {
        $firstl = strtolower($first->getFilterLabel());
        $secondl = strtolower($second->getFilterLabel());

        if ($firstl == $secondl)
        {
            return 0;
        }

        return ($firstl > $secondl) ? +1 : -1;
    }

}
