<?php

namespace Component\Grid;

use DataHandle\Request;

class GroupHelper
{

    /**
     * Create the group dataSource
     * @param \Page\Page $page page
     * @return \DataSource\QueryBuilder datasource
     */
    public static function getDataSource(\Page\Page $page)
    {
        $methods = \Component\Grid\GroupHelper::listAggrMethods();
        $model = $page->getModel();
        $gridGroupBy = Request::get('grid-groupby-field');
        $gridAggrBy = Request::get('grid-aggrby-field');

        $groupBy = [];
        $sqlColumns = [];

        foreach ($gridGroupBy as $columnName)
        {
            $dbColumn = $model->getColumn($columnName);

            $sqlColumns[] = $columnName;

            if ($dbColumn instanceof \Db\Column\Column)
            {
                if ($dbColumn->getReferenceTable())
                {
                    $sqlColumns[] = $dbColumn->getReferenceSql(TRUE);
                }
            }

            $groupBy[] = $columnName;
        }

        $aggregators = [];

        foreach ($gridAggrBy as $value)
        {
            $explode = explode('--', $value);
            $method = $explode[0];
            $columnName = $explode[1];
            $dbColumn = $model->getColumn($columnName);

            $columnLabel = $methods[$method] . ' de ' . $dbColumn->getLabel();
            $sqlColumns[] = $method . '(' . $columnName . ') AS "' . $columnLabel . '"';

            $aggregators[] = new \DataSource\Aggregator($columnLabel, $method);
        }

        $queryBuilder = $model::query();
        $queryBuilder instanceof \Db\QueryBuilder;
        $queryBuilder->setColumns($sqlColumns);
        $queryBuilder->setGroupBy(implode(',', $groupBy));

        $dataSource = new \DataSource\QueryBuilder($queryBuilder);
        $dataSource->addAggregator($aggregators);

        $page->addFiltersToDataSource($dataSource);
        $columns = $dataSource->getColumns();

        $grid = $page->getGrid();
        $grid->setDataSource($dataSource);
        $grid->setColumns($columns);

        return $dataSource;
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

    public static function createColumnOptions($columns)
    {
        $options = [];

        foreach ($columns as $column)
        {
            $column instanceof \Component\Grid\Column;
            $option = new \stdClass();
            $option->value = $column->getName();
            $option->label = $column->getLabel();
            $options[$column->getLabel()] = $option;
        }

        ksort($options);
        return $options;
    }

    public static function createPopup(\Page\Page $page)
    {
        $grid = $page->getGrid();
        $originalDataSource = $grid->getDataSourceOriginal();
        $columns = $originalDataSource->getColumns();
        $options = self::createColumnOptions($columns);
        $left[] = new \View\H3(null, 'Agrupar por');
        $left[] = new \View\Select('gridGroupBy', $options, null, 'column-12');
        $left[] = $btn = new \View\Ext\Button('btnAddGroup', 'plus', 'Adicionar agrupamento', 'gridGroupAddGroup', 'clean small');
        $btn->css('border', 'none');
        $left[] = $leftHolder = new \View\Div('leftHolder', null);
        $leftHolder->css('margin-top', '30px');

        $right[] = new \View\H3(null, 'Mostrar agregação');
        $right[] = new \View\Select('gridAggrBy', $options, null, 'column-6');
        $right[] = new \View\Select('gridAggrMethods', \Component\Grid\GroupHelper::listAggrMethods(), null, 'column-6');

        $right[] = $btn = new \View\Ext\Button('btnAddAggr', 'plus', 'Adicionar agregação', 'gridGroupAddAggr', 'clean small');
        $btn->css('border', 'none');
        $right[] = $rightHolder = new \View\Div('rightHolder', null);
        $rightHolder->css('margin-top', '30px');

        $content[] = new \View\Div('left', $left, 'column-p-6');
        $content[] = new \View\Div('right', $right, 'column-p-6');

        $buttons = [];

        $url = "g('{$page->getPageUrl()}');";

        $buttons[] = new \View\Ext\Button('ok', 'check', 'Executar', $url, 'primary');

        $popup = new \View\Blend\Popup('popupAggr', 'Agrupamento de dados', $content, $buttons, 'form');

        self::createLoadedInputs($page);

        return $popup;
    }

    public static function createLoadedInputs(\Page\Page $page)
    {
        $gridGroupBy = Request::get('grid-groupby-field');
        $gridAggrBy = Request::get('grid-aggrby-field');
        $grid = $page->getGrid();
        $originalDatasource = $grid->getDataSourceOriginal();
        $columns = $originalDatasource->getColumns();

        if (is_array($gridGroupBy))
        {
            foreach ($gridGroupBy as $groupBy)
            {
                $column = $columns[$groupBy];
                $div = self::createFieldGroupBy($column);
                $page->byId('leftHolder')->append($div);
            }
        }

        if (is_array($gridAggrBy))
        {
            foreach ($gridAggrBy as $aggr)
            {
                $explode = explode('--', $aggr);
                $method = $explode[0];
                $columName = $explode[1];
                $column = $columns[$columName];
                $div = self::createFieldAggr($column, $method);
                $page->byId('rightHolder')->append($div);
            }
        }

        \Log::dump($gridGroupBy);
        \Log::dump($gridAggrBy);
    }

    public static function createFieldGroupBy(\Component\Grid\Column $column)
    {
        $columName = $column->getName();
        $idField = 'grid-groupby-field-' . $columName;

        $content = [];
        $content[] = new \View\Input('grid-groupby-field[' . $columName . ']', 'hidden', $columName);

        $content[] = $column->getLabel();
        $content[] = $btnRemove = new \View\Ext\Icon('trash', null, "$(this).parent().remove();");
        $btnRemove->css('float', 'right');

        $div = new \View\Div($idField, $content, 'column-12');

        return $div;
    }

    public static function createFieldAggr(\Component\Grid\Column $column, $method)
    {
        $methods = \Component\Grid\GroupHelper::listAggrMethods();
        $columName = $column->getName();
        $label = $methods[$method] . ' - ' . $column->getLabel();
        $idField = 'grid-aggrby-field-' . $columName;
        $value = $method . '--' . $columName;

        $content = [];
        $content[] = new \View\Input('grid-aggrby-field[' . $value . ']', 'hidden', $value);

        $content[] = $label;
        $content[] = $btnRemove = new \View\Ext\Icon('trash', null, "$(this).parent().remove();");
        $btnRemove->css('float', 'right');

        $div = new \View\Div($idField, $content, 'column-12');

        return $div;
    }

    public static function popupAddGroup(\Page\Page $page)
    {
        $gridGroupBy = Request::get('gridGroupBy');
        $grid = $page->getGrid();
        $columns = $grid->getDataSourceOriginal()->getColumns();
        $column = $columns[$gridGroupBy];

        if (!$column)
        {
            throw new \UserException('Impossível encontrar coluna ' . $gridGroupBy);
        }

        $selecionados = Request::get('grid-groupby-field');

        if (isset($selecionados[$gridGroupBy]))
        {
            throw new \UserException('Campo \'' . $column->getLabel() . '\' já adicionado ao agrupamento.');
        }

        $div = self::createFieldGroupBy($column);

        $page->byId('leftHolder')->append($div);
        $page->byId('gridGroupBy')->val('');
    }

    public static function popupAddAggr(\Page\Page $page)
    {
        $columnName = Request::get('gridAggrBy');
        $method = Request::get('gridAggrMethods');

        if (!$columnName || !$method)
        {
            throw new \UserException('Selecione ambos parametros!');
        }

        $grid = $page->getGrid();
        $columns = $grid->getDataSourceOriginal()->getColumns();
        $column = $columns[$columnName];

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

}
