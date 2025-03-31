<?php

namespace Component\Grid;

use DataHandle\Request;

/**
 * Helper to generate Grouped datasources
 */
class GroupHelper
{

    /**
     * Safe any name
     * @param string $name name
     * @return string safe name
     */
    public static function safeName($name)
    {
        return \Type\Text::get(strip_tags($name . ''))->toFile() . '';
    }

    /**
     * List possible aggregations methods
     *
     * @return array
     */
    public static function listAggrMethods()
    {
        $methods = [];
        $methods['sum'] = 'Soma';
        $methods['max'] = 'Máximo';
        $methods['min'] = 'Mínimo';
        $methods['avg'] = 'Média';
        $methods['count'] = 'Contagem';

        return $methods;
    }

    protected static function getRelationsIndexed($model)
    {
        $relations = $model->getRelations();
        $result = [];

        if (is_array($relations))
        {
            foreach ($relations as $relation)
            {
                $result[self::safeName($relation->getLabel())] = $relation;
            }
        }

        return $result;
    }

    /**
     * Create the group dataSource
     * @param \Page\Page $page page
     * @return \DataSource\QueryBuilder datasource
     */
    public static function getGroupedDataSource(\Page\Page $page = null)
    {
        $page = $page ? $page : \View\View::getDom();
        $grid = $page->getGrid();
        $methods = \Component\Grid\GroupHelper::listAggrMethods();
        $groupColumns = $grid->getAllColumns();
        $model = $page->getModel();
        $modelGroupName = self::safeName($model->getLabel());
        $modelColumns = $model->getColumns();
        $relations = self::getRelationsIndexed($model);
        $gridGroupBy = Request::get('grid-groupby-field');
        $gridAggrBy = Request::get('grid-aggrby-field');

        $groupBy = [];
        $sqlColumns = [];
        $gridColumns = [];
        $aggregators = [];
        $queryBuilder = $model::query();
        $queryBuilder instanceof \Db\QueryBuilder;

        if (is_array($gridGroupBy))
        {
            foreach ($gridGroupBy as $columnName)
            {
                $explode = explode('.', $columnName);
                $groupName = $explode[0];
                $simpleColumnName = $explode[1];

                if (!isset($groupColumns[$groupName][$simpleColumnName]))
                {
                    continue;
                }

                $gridColumn = $groupColumns[$groupName][$simpleColumnName];
                $gridColumn instanceof \Component\Grid\Column;
                $sqlColumns = array_merge($sqlColumns, self::getUserDefinedColumn($gridColumn));
                $gridColumns[$gridColumn->getName()] = $gridColumn;
                self::addLeftJoin($queryBuilder, $relations, $groupName);

                $groupBy[] = $gridColumn->getSql();
            }
        }

        //groupments
        if (is_array($gridAggrBy))
        {
            foreach ($gridAggrBy as $value)
            {
                $explode = explode('--', $value);
                $method = $explode[0];
                $columnName = $explode[1];
                $explode2 = explode('.', $columnName);
                $groupName = $explode2[0];
                $simpleColumnName = $explode2[1];

                //if columns does not exists, ignore it
                if (!isset($groupColumns[$groupName][$simpleColumnName]))
                {
                    continue;
                }

                $dbColumn = null;
                $type = null;

                if ($groupName == $modelGroupName)
                {
                    $dbColumn = isset($modelColumns[$simpleColumnName]) ? $modelColumns[$simpleColumnName] : null;
                }

                $gridColumn = clone($groupColumns[$groupName][$simpleColumnName]);
                $gridColumn instanceof \Component\Grid\Column;

                //grouped columns can't be pk edit column
                if ($gridColumn instanceof \Component\Grid\PkColumnEdit)
                {
                    $gridColumn = \DataSource\ColumnConvert::gridPkColumnToSimple($gridColumn);
                    $gridColumn->setAlign('alignRight');
                }

                $columnLabel = '<small>' . $methods[$method] . ' de </small><br/> <small>' . $gridColumn->getGroupName() . '</small> - ' . $gridColumn->getLabel();
                $columnLabelSafe = self::safeName($methods[$method] . '_' . $gridColumn->getGroupName() . '_' . $gridColumn->getName());

                $gridColumn->setLabel($columnLabel);
                $gridColumn->setName($columnLabelSafe);
                $gridColumn->setUserAdded(true);
                $gridColumns[$columnLabelSafe] = $gridColumn;

                $columnSql = $groupName . '.' . $simpleColumnName;

                if ($dbColumn)
                {
                    $columnSql = $model::getTableName() . '.' . $simpleColumnName;
                    $type = $dbColumn->getType();

                    if ($dbColumn instanceof \Db\Column\Search)
                    {
                        $sql = $dbColumn->getSql(FALSE);
                        $columnSql = $sql[0];
                    }
                }

                if ($type == \Db\Column\Column::TYPE_TIME)
                {
                    $sqlColumn = 'SEC_TO_TIME(' . $method . '( TIME_TO_SEC( ' . $columnSql . '))) AS "' . $columnLabelSafe . '"';
                }
                else
                {
                    $sqlColumn = $method . '(' . $columnSql . ') AS "' . $columnLabelSafe . '"';
                }

                $sqlColumns[] = $sqlColumn;
                self::addLeftJoin($queryBuilder, $relations, $groupName);

                //correct method to aggregation
                $aggrMethod = $method == 'count' ? 'sum' : $method;
                $aggregators[] = new \DataSource\Aggregator($columnLabelSafe, $aggrMethod);
            }
        }

        $queryBuilder->setColumns($sqlColumns);
        $queryBuilder->setGroupBy(implode(',', $groupBy));

        $dataSource = new \DataSource\QueryBuilder($queryBuilder);
        $dataSource->addAggregator($aggregators);
        $dataSource->setColumns($gridColumns);
        //important to keep page filtred values
        $page->addFiltersToDataSource($dataSource);
        $grid->setDataSource($dataSource);

        return $dataSource;
    }

    public static function addLeftJoin(\Db\QueryBuilder $queryBuilder, array $relations, $groupName)
    {
        if (isset($relations[$groupName]))
        {
            $relation = $relations[$groupName];
            $sql = str_replace($relation->getTableName(), $groupName, $relation->getSql());

            if (!$queryBuilder->joinExistsAlias($groupName))
            {
                $queryBuilder->leftJoin($relation->getTableName(), $sql, $groupName);
            }
        }

        return $queryBuilder;
    }

    public static function getGroupedDataColumn(\Component\Grid\Column $gridColumn, \Db\Column\Column $dbColumn = null)
    {
        $sqlColumns = [];
        $columnLabel = '<small>' . $gridColumn->getGroupName() . '</small><br/>' . $gridColumn->getLabel();
        $gridColumn->setLabel($columnLabel);
        $columnLabelSafe = self::safeName($gridColumn->getGroupName() . '_' . $gridColumn->getName());

        $modelName = $gridColumn->getModelName();
        $tableName = $modelName::getTableName();
        $columnSql = $tableName . '.' . $gridColumn->getSql();

        $sqlColumns[] = $columnSql . ' AS ' . $columnLabelSafe;

        if ($dbColumn instanceof \Db\Column\Column)
        {
            if ($dbColumn->getReferenceTable())
            {
                $sqlColumns[] = $dbColumn->getReferenceSql(false) . ' AS ' . $columnLabelSafe . 'Description';
            }
        }

        return $sqlColumns;
    }

    public static function parseData(\Page\Page $page, \DataSource\QueryBuilder $dataSource)
    {
        $model = $page->getModel();
        //grouped grids don't need limit e offset
        $dataSource->setLimit(null)->setOffset(null);
        $data = $dataSource->getData();
        $columns = $dataSource->getColumns();

        foreach ($data as $obj)
        {
            foreach ($columns as $column)
            {
                $value = \DataSource\Grab::getDbValue($column->getName(), $obj);
                $dbColumn = $column->getDbColumn() ? $column->getDbColumn() : $model->getColumn($column->getName());

                if ($dbColumn instanceof \Db\Column\Column)
                {
                    if ($dbColumn->getReferenceTable())
                    {
                        $propertyDescription = $dbColumn->getName() . 'Description';
                        $valueDescription = \DataSource\Grab::getDbValue($propertyDescription, $obj);

                        if (!$valueDescription)
                        {
                            if ($obj instanceof \Db\Model)
                            {
                                $obj->setValue($propertyDescription, 'Sem ' . lcfirst($dbColumn->getLabel()));
                            }
                        }
                    }
                    else if ($dbColumn->getConstantValues())
                    {
                        $propertyDescription = $dbColumn->getName() . 'Description';
                        $cValues = $dbColumn->getConstantValuesArray();

                        if (isset($cValues[$value]))
                        {
                            $value = $cValues[$value];
                        }

                        $obj->setValue($propertyDescription, $value);
                    }
                }
            }
        }

        return $data;
    }

    /**
     * Create a new user defined datasource
     *
     * @param \DataSource\DataSource $dataSource original datasource
     * @return \DataSource\DataSource user defined datasource
     */
    public static function getUserDefinedDataSource()
    {
        $page = \View\View::getDom();
        $grid = $page->getGrid();
        $extraColumns = Request::get('grid-addcolumn-field');
        $groupColumns = $grid->getAllColumns();
        $model = $page->getModel();
        $modelGroupName = self::safeName($model->getLabel());
        $modelColumns = $model->getColumns();
        $relations = self::getRelationsIndexed($model);

        $queryBuilder = $model::query();
        $queryBuilder instanceof \Db\QueryBuilder;
        $userDataSource = new \DataSource\QueryBuilder($queryBuilder);
        $page->addFiltersToDataSource($userDataSource);
        //clean the columns of the datasource, so we can use only defined columns
        $userDataSource->setColumns(null);

        if (!is_array($extraColumns))
        {
            return null;
        }

        $sqlColumns = [];

        foreach ($extraColumns as $extraColumm)
        {
            $explode = explode('.', $extraColumm);
            $groupName = $explode[0];
            $simpleColumnName = $explode[1];

            if (isset($groupColumns[$groupName][$simpleColumnName]))
            {
                $gridColumn = $groupColumns[$groupName][$simpleColumnName];
                $sqlColumns = array_merge($sqlColumns, self::getUserDefinedColumn($gridColumn));
                $userDataSource->addColumn($gridColumn);
            }

            self::addLeftJoin($queryBuilder, $relations, $groupName);
        }

        $queryBuilder->setColumns($sqlColumns);
        return $userDataSource;
    }

    /**
     * Create one column to DataDource
     * @param \Component\Grid\Column $gridColumn grid column
     * @param \Db\Column\Column $dbColumn db column
     * @return array the result sql
     */
    public static function getUserDefinedColumn(\Component\Grid\Column $gridColumn)
    {
        $page = \View\View::getDom();
        $grid = $page->getGrid();
        $dbModel = $grid->getDbModel();
        $dbColumn = $dbColumn = $gridColumn->getDbColumn();
        $sqlColumns = [];
        //safe name / column alias
        $columnAlias = self::safeName($gridColumn->getGroupName()) . '_' . $gridColumn->getName();
        $modelName = $gridColumn->getModelName();
        $tableName = $modelName::getTableName();

        //activated column, and mark as user addded
        $gridColumn->setUserAdded(true);
        $gridColumn->setRender(true);
        $gridColumn->setLabel('<small>' . $gridColumn->getGroupName() . '</small><br/>' . $gridColumn->getLabel());

        //define the sql for column
        if (get_class($dbModel) == $modelName)
        {
            $columnSql = $tableName . '.' . $gridColumn->getSql();
        }
        else
        {
            $columnSql = self::safeName($gridColumn->getGroupName()) . '.' . $gridColumn->getSql();
        }

        $gridColumn->setSql($columnSql);
        $gridColumn->setName($columnAlias);

        //search column
        if ($dbColumn instanceof \Db\Column\Search)
        {
            $sql = $dbColumn->getSql(FALSE);
            $sqlColumns[] = $sql[0] . ' AS ' . $columnAlias;
        }
        //simple column
        else
        {
            $sqlColumns[] = $columnSql . ' AS ' . $columnAlias;
        }

        //references columns
        if ($dbColumn instanceof \Db\Column\Column)
        {
            if ($dbColumn->getReferenceTable())
            {
                $sqlColumns[] = $dbColumn->getReferenceSql(FALSE) . ' AS ' . $columnAlias . 'Description';
            }
        }

        return $sqlColumns;
    }

}
