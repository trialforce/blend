<?php

namespace Component\Grid;

use DataHandle\Request;

class GroupHelper
{

    /**
     * Safe any name
     * @param string $name name
     * @return string safe name
     */
    public static function safeName($name)
    {
        return \Type\Text::get(strip_tags($name))->toFile() . '';
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
        $methods = \Component\Grid\GroupHelper::listAggrMethods();
        $groupColumns = self::getAllColumns($page);
        $model = $page->getModel();
        $modelGroupName = self::safeName($model->getLabel());
        $modelColumns = $model->getColumns();
        $relations = self::getRelationsIndexed($model);
        $gridGroupBy = Request::get('grid-groupby-field');
        $gridAggrBy = Request::get('grid-aggrby-field');

        $groupBy = [];
        $sqlColumns = [];
        $aggregators = [];
        $queryBuilder = $model::query();
        $queryBuilder instanceof \Db\QueryBuilder;

        //store column labels to adjust in end
        $columnLabels = [];

        foreach ($gridGroupBy as $columnName)
        {
            $explode = explode('.', $columnName);
            $groupName = $explode[0];
            $simpleColumnName = $explode[1];
            $dbColumn = null;

            if ($groupName == $modelGroupName && issset($modelColumns[$simpleColumnName]))
            {
                $dbColumn = $modelColumns[$simpleColumnName];
            }

            if (isset($groupColumns[$groupName][$simpleColumnName]))
            {
                $gridColumn = $groupColumns[$groupName][$simpleColumnName];
            }

            $sqlColumns = array_merge($sqlColumns, self::getUserDefinedColumn($gridColumn, $dbColumn));
            $columnLabels[$gridColumn->getName()] = $gridColumn->getLabel();

            self::addLeftJoin($queryBuilder, $relations, $groupName);

            $groupBy[] = $columnName;
        }

        //groupments
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

            if ($groupName == $modelGroupName)
            {
                $dbColumn = isset($modelColumns[$simpleColumnName]) ? $modelColumns[$simpleColumnName] : null;
            }

            $gridColumn = $groupColumns[$groupName][$simpleColumnName];

            $columnLabel = $methods[$method] . ' de <br/> <small>' . $gridColumn->getGroupName() . '</small> - ' . $gridColumn->getLabel();
            $columnLabelSafe = self::safeName($columnLabel);

            $columnSql = $groupName . '.' . $simpleColumnName;

            if ($dbColumn)
            {
                $columnSql = $model::getTableName() . '.' . $simpleColumnName;
            }

            $sqlColumns[] = $method . '(' . $columnSql . ') AS "' . $columnLabelSafe . '"';
            //store to use in the end
            $columnLabels[$columnLabelSafe] = $columnLabel;

            self::addLeftJoin($queryBuilder, $relations, $groupName);

            //correct method to aggregation
            $aggrMethod = $method == 'count' ? 'sum' : $method;
            $aggregators[] = new \DataSource\Aggregator($columnLabelSafe, $aggrMethod);
        }

        $queryBuilder->setColumns($sqlColumns);
        $queryBuilder->setGroupBy(implode(',', $groupBy));

        $dataSource = new \DataSource\QueryBuilder($queryBuilder);
        $dataSource->addAggregator($aggregators);

        $page->addFiltersToDataSource($dataSource);
        $columns = $dataSource->getColumns();

        foreach ($columnLabels as $columnLabelSafe => $columnLabel)
        {
            $column = $dataSource->getColumn($columnLabelSafe);

            if ($column)
            {
                $column->setLabel($columnLabel);
            }
        }

        $grid = $page->getGrid();
        $grid->setDataSource($dataSource);
        $grid->setColumns($columns);

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
        //a try
        //$columnLabel = '<small>' . ucfirst($groupName) . '</small><br/>' . ucfirst($simpleColumnName);
        $columnLabel = '<small>' . $gridColumn->getGroupName() . '</small><br/>' . $gridColumn->getLabel();
        $gridColumn->setLabel($columnLabel);
        $columnLabelSafe = self::safeName($gridColumn->getGroupName() . '_' . $gridColumn->getName());
        //store to use in the end
        //$columnLabels[$columnLabelSafe] = $columnLabel;

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
        $data = $dataSource->getData();
        $columns = $dataSource->getColumns();

        foreach ($data as $obj)
        {
            foreach ($columns as $column)
            {
                $value = \DataSource\Grab::getDbValue($column->getName(), $obj);
                $dbColumn = $model->getColumn($column->getName());

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
                }
            }
        }

        return $data;
    }

    /**
     * Mount collumn list options for group
     *
     * @param array $columnGroup \Component\Grid\Column column list list
     * @return array array of stdClass
     */
    public static function createColumnOptions($columnGroup)
    {
        foreach ($columnGroup as $columnGroupLabelSafe => $columns)
        {
            $arrayColumns = array_values($columns);
            $firstColumn = $arrayColumns[0];
            $columnGroupLabel = $firstColumn->getGroupName();
            $options = [];

            $optgroup[] = $opt = new \View\OptGroup($columnGroupLabelSafe, $columnGroupLabel);

            foreach ($columns as $column)
            {
                $column instanceof \Component\Grid\Column;
                $columnName = $columnGroupLabelSafe . '.' . $column->getName();
                $option = new \View\Option($columnName, $column->getLabel());
                $options[$column->getLabel()] = $option;
            }

            $opt->html($options);
        }

        ksort($options);

        return $optgroup;
    }

    protected static function getAllColumns(\Page\Page $page)
    {
        if (method_exists($page, 'getModel'))
        {
            $dbModel = $page->getModel();
            $columnGroup = \DataSource\ColumnConvert::dbToGridAllGrouped($dbModel);
        }
        else
        {
            $datasource = $page->getDatasource();
            $columns = $datasource->getColumns();
            $title = $page->getTitle() ? self::safeName($page->getTitle()) : 'Colunas';

            $columnGroup[$title] = $columns;
        }

        if (method_exists($page, 'setDefaultGroups'))
        {
            $columnGroup = $page->setDefaultGroups($columnGroup);
        }

        return $columnGroup;
    }

    public static function createColumns(\Page\Page $page = null)
    {
        $page = $page ? $page : \View\View::getDom();
        $grid = $page->getGrid();

        $columns = self::getAllColumns($page);
        $left[] = new \View\H3(null, 'Adicionar colunas');
        $left[] = $select = new \View\Div(null, new \View\Select('addColumn', self::createColumnOptions($columns), null, 'column-6'), 'column-12');
        $left[] = $btn = new \View\Ext\Button('btnAddColumn', 'plus', 'Adicionar coluna', 'gridGroupAddColumn', 'clean small');
        $btn->css('border', 'none');

        $content[] = new \View\Div('columns-definition', $left, 'column-p-12');
        $content[] = new \View\Div('columns-holder', null, 'columns-holder column-p-6');

        $extraColumns = Request::get('grid-addcolumn-field');
        $elements = [];

        //allready customized columns
        if (is_array($extraColumns))
        {
            foreach ($extraColumns as $extraColumn)
            {
                $explode = explode('.', $extraColumn);
                $columnGroup = $explode[0];
                $columnName = $explode[1];

                if (isset($columns[$columnGroup][$columnName]))
                {
                    $column = $columns[$columnGroup][$columnName];
                    $elements[] = self::createFieldColumn($column);
                }
            }
        }
        //starting columns to be customized
        else
        {
            $mainColumns = $grid->getDataSourceOriginal()->getColumns();

            foreach ($mainColumns as $column)
            {
                if ($column->getRender())
                {
                    $elements[] = self::createFieldColumn($column);
                }
            }
        }

        $page->byId('columns-holder')->append($elements);

        return $content;
    }

    public static function createContent(\Page\Page $page = null)
    {
        $page = $page ? $page : \View\View::getDom();
        $columns = self::getAllColumns($page);
        $left[] = new \View\H3(null, 'Agrupar por');
        $left[] = $select = new \View\Select('gridGroupBy', self::createColumnOptions($columns), null, 'column-12');
        //$select->click('alert()');

        $left[] = $btn = new \View\Ext\Button('btnAddGroup', 'plus', 'Adicionar agrupamento', 'gridGroupAddGroup', 'clean small');
        $btn->css('border', 'none');
        $left[] = $leftHolder = new \View\Div('leftHolder', null, 'column-12 grid-group-by-left-holder');

        $right[] = new \View\H3(null, 'Mostrar agregação');
        $right[] = new \View\Select('gridAggrBy', self::createColumnOptions($columns), null, 'column-6');
        $right[] = new \View\Select('gridAggrMethods', \Component\Grid\GroupHelper::listAggrMethods(), null, 'column-6');

        $right[] = $btn = new \View\Ext\Button('btnAddAggr', 'plus', 'Adicionar agregação', 'gridGroupAddAggr', 'clean small');
        $btn->css('border', 'none');
        $right[] = $rightHolder = new \View\Div('rightHolder', null, 'column-12 grid-group-by-right-holder');

        $content[] = new \View\Div('left', $left, 'column-p-6');
        $content[] = new \View\Div('right', $right, 'column-p-6');

        self::createLoadedInputs($page);

        return $content;
    }

    public static function createLoadedInputs(\Page\Page $page)
    {
        $gridGroupBy = Request::get('grid-groupby-field');
        $gridAggrBy = Request::get('grid-aggrby-field');

        $groupColumns = self::getAllColumns($page);

        $elements = [];

        if (is_array($gridGroupBy))
        {
            foreach ($gridGroupBy as $groupBy)
            {
                $explode = explode('.', $groupBy);
                $columnGroup = $explode[0];
                $columnName = $explode[1];
                $column = $groupColumns[$columnGroup][$columnName];
                $elements[] = self::createFieldGroupBy($column);
            }
        }

        $page->byId('leftHolder')->append($elements);
        $elements = [];

        if (is_array($gridAggrBy))
        {
            foreach ($gridAggrBy as $aggr)
            {
                $explode = explode('--', $aggr);
                $method = $explode[0];
                $columNameGrouped = explode('.', $explode[1]);
                $columnGroup = $columNameGrouped[0];
                $columnName = $columNameGrouped[1];
                $column = $groupColumns[$columnGroup][$columnName];
                $elements[] = self::createFieldAggr($column, $method);
            }
        }

        $page->byId('rightHolder')->append($elements);
    }

    /**
     * Create a column field
     *
     * @param \Component\Grid\Column $column
     * @return \View\Div
     */
    public static function createFieldColumn(\Component\Grid\Column $column)
    {
        $columName = self::safeName($column->getGroupName()) . '.' . $column->getName();
        $label = $column->getLabel();

        if ($column->getGroupName())
        {
            $label = $column->getGroupName() . ' - ' . $label;
        }

        $idField = 'grid-addcolumn-field-' . $columName;
        $value = $columName;

        $content = [];
        $content[] = new \View\Input('grid-addcolumn-field[' . $value . ']', 'hidden', $value);

        $content[] = $label;

        if (!$column instanceof \Component\Grid\PkColumnEdit)
        {
            $content[] = new \View\Ext\Icon('trash', null, "return gridAddColumnRemove(this)", 'grid-addcolumn-icon');
            $content[] = new \View\Ext\Icon('arrow-down', null, "return gridAddColumnDown(this)", 'grid-addcolumn-icon');
            $content[] = new \View\Ext\Icon('arrow-up', null, "return gridAddColumnUp(this)", 'grid-addcolumn-icon');
        }

        $div = new \View\Div($idField, $content, 'grid-addcolumn-field column-12');

        return $div;
    }

    /**
     * Crete a group by filter
     *
     * @param \Component\Grid\Column $column
     * @return \View\Div
     */
    public static function createFieldGroupBy(\Component\Grid\Column $column)
    {
        $columName = self::safeName($column->getGroupName()) . '.' . $column->getName();
        $idField = 'grid-groupby-field-' . $columName;

        $content = [];
        $content[] = new \View\Input('grid-groupby-field[' . $columName . ']', 'hidden', $columName);

        $content[] = $column->getGroupName() . ' - ' . $column->getLabel();
        $content[] = $btnRemove = new \View\Ext\Icon('trash', null, "$(this).parent().remove();", 'trashFilter');

        $div = new \View\Div($idField, $content, 'column-12 grid-addcolumn-field');

        return $div;
    }

    /**
     * Create a aggregation field
     *
     * @param \Component\Grid\Column $column
     * @param type $method
     * @return \View\Div
     */
    public static function createFieldAggr(\Component\Grid\Column $column, $method)
    {
        $methods = \Component\Grid\GroupHelper::listAggrMethods();
        $columName = self::safeName($column->getGroupName()) . '.' . $column->getName();
        $label = $methods[$method] . ' - ' . $column->getLabel();
        $idField = 'grid-aggrby-field-' . $columName;
        $value = $method . '--' . $columName;

        $content = [];
        $content[] = new \View\Input('grid-aggrby-field[' . $value . ']', 'hidden', $value);

        $content[] = $label;
        $content[] = $btnRemove = new \View\Ext\Icon('trash', null, "$(this).parent().remove();", 'trashFilter');

        $div = new \View\Div($idField, $content, 'column-12 grid-addcolumn-field');

        return $div;
    }

    /**
     * Action called when user is adding a column
     *
     * @param \Page\Page $page
     * @throws \UserException
     */
    public static function gridGroupAddColumn(\Page\Page $page)
    {
        $addColumn = Request::get('addColumn');

        if (!$addColumn)
        {
            throw new \UserException('Selecione uma coluna!');
        }

        $explode = explode('.', $addColumn);
        $columnGroup = $explode[0];
        $columnName = $explode[1];
        $groupColumns = self::getAllColumns($page);
        $column = $groupColumns[$columnGroup][$columnName];

        if (!$column)
        {
            throw new \UserException('Impossível encontrar coluna ' . $addColumn);
        }

        $selecionados = Request::get('grid-addcolumn-field');

        if (isset($selecionados[$addColumn]))
        {
            throw new \UserException('Campo \'' . $column->getGroupName() . ' - ' . $column->getLabel() . '\' já adicionado ao agrupamento.');
        }

        $div = self::createFieldColumn($column);

        $page->byId('columns-holder')->append($div);
        $page->byId('addColumn')->val('');
    }

    /**
     * Action called when user add a group
     * @param \Page\Page $page
     * @throws \UserException
     */
    public static function popupAddGroup(\Page\Page $page)
    {
        $gridGroupBy = Request::get('gridGroupBy');

        if (!$gridGroupBy)
        {
            throw new \UserException('Selecione uma coluna!');
        }

        $explode = explode('.', $gridGroupBy);
        $columnGroup = $explode[0];
        $columnName = $explode[1];
        $groupColumns = self::getAllColumns($page);
        $column = $groupColumns[$columnGroup][$columnName];

        if (!$column)
        {
            throw new \UserException('Impossível encontrar coluna ' . $gridGroupBy);
        }

        $selecionados = Request::get('grid-groupby-field');

        if (isset($selecionados[$gridGroupBy]))
        {
            throw new \UserException('Campo \'' . $column->getGroupName() . ' - ' . $column->getLabel() . '\' já adicionado ao agrupamento.');
        }

        $div = self::createFieldGroupBy($column);

        $page->byId('leftHolder')->append($div);
        $page->byId('gridGroupBy')->val('');
    }

    public static function popupAddAggr(\Page\Page $page)
    {
        $gridAggrBy = Request::get('gridAggrBy');
        $method = Request::get('gridAggrMethods');

        if (!$gridAggrBy || !$method)
        {
            throw new \UserException('Selecione ambos parametros!');
        }

        $grid = $page->getGrid();
        $columns = $grid->getDataSourceOriginal()->getColumns();
        $explode = explode('.', $gridAggrBy);
        $columnGroup = $explode[0];
        $columnName = $explode[1];
        $groupColumns = self::getAllColumns($page);
        $column = $groupColumns[$columnGroup][$columnName];

        if (!$column)
        {
            throw new \UserException('Impossível encontrar coluna ' . $columnName);
        }

        $posted = Request::get('grid-aggrby-field');
        $value = $method . '--' . $columnName;

        if (isset($posted[$value]))
        {
            throw new \UserException('Campo já adicionado ao agrupamento.');
        }

        $div = self::createFieldAggr($column, $method);

        $page->byId('rightHolder')->append($div);
        $page->byId('gridAggrBy')->val('');
        $page->byId('gridAggrMethods')->val('');
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
        $extraColumns = Request::get('grid-addcolumn-field');
        $groupColumns = self::getAllColumns($page);
        $model = $page->getModel();
        $modelGroupName = self::safeName($model->getLabel());
        $modelColumns = $model->getColumns();
        $relations = self::getRelationsIndexed($model);

        $queryBuilder = $model::query();
        $queryBuilder instanceof \Db\QueryBuilder;
        $userDataSource = new \DataSource\QueryBuilder($queryBuilder);

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
            $dbColumn = null;

            if ($groupName == $modelGroupName)
            {
                $dbColumn = isset($modelColumns[$simpleColumnName]) ? $modelColumns[$simpleColumnName] : null;
            }

            if (isset($groupColumns[$groupName][$simpleColumnName]))
            {
                $gridColumn = $groupColumns[$groupName][$simpleColumnName];
                $sqlColumns = array_merge($sqlColumns, self::getUserDefinedColumn($gridColumn, $dbColumn));
                $userDataSource->addColumn($gridColumn);
            }

            self::addLeftJoin($queryBuilder, $relations, $groupName);
        }

        \Log::debug($userDataSource);

        $queryBuilder->setColumns($sqlColumns);
        return $userDataSource;
    }

    public static function getUserDefinedColumn(\Component\Grid\Column $gridColumn, \Db\Column\Column $dbColumn = null)
    {
        $sqlColumns = [];
        $columnAlias = self::safeName($gridColumn->getGroupName()) . '_' . $gridColumn->getName();
        $modelName = $gridColumn->getModelName();
        $tableName = $modelName::getTableName();

        $gridColumn->setUserAdded(true);
        $gridColumn->setRender(true);
        $gridColumn->setLabel('<small>' . $gridColumn->getGroupName() . '</small><br/>' . $gridColumn->getLabel());

        if ($dbColumn)
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
