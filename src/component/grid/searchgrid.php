<?php

namespace Component\Grid;

use DataHandle\Request;

/**
 * Search grid used in search/query crud forms
 *
 * @author eduardo
 */
class SearchGrid extends \Component\Grid\Grid
{

    /**
     * Call interface functions.
     * Used for optimizations
     *
     * @var boolean
     */
    protected $callInterfaceFunctions = TRUE;

    /**
     * Extra filters
     * @var array
     */
    protected $filters = array();

    /**
     * Tabs to create
     * @var array
     */
    protected $createTabs;

    /**
     * auto filters created
     * @var bool
     */
    protected $autoFiltersCreated = false;

    /**
     * List of columsn for groupment
     * @var array list of columsn for groupment
     */
    protected $columnsGroup;

    /**
     * List of columsn grid customization
     * @var array list of columsn for grid customization
     */
    protected $columnsCustomize;

    public function __construct($id = NULL, $dataSource = NULL, $class = 'grid', $columns = NULL)
    {
        $myId = $id ? $id : get_class($this);
        parent::__construct($myId, $dataSource, $class);

        //solve a creep bug
        if ($columns)
        {
            $this->setColumns($columns);
        }

        $this->setCreateAllTabs();
    }

    public function isGrouped()
    {
        return \DataHandle\Request::get('grid-groupby-field') ? true : false;
    }

    public function isUserAddedColumns()
    {
        return \DataHandle\Request::get('grid-addcolumn-field') ? true : false;
    }

    public function isCustomized()
    {
        return $this->isGrouped() || $this->isUserAddedColumns();
    }

    public function setCreateAllTabs()
    {
        $this->setCreateTabFilter(true);
        $this->setCreateTabColumn(true);
        $this->setCreateTabGroup(true);
        $this->setCreateTabSave(true);
    }

    public function setCreateTabFilter($create)
    {
        return $this->setCreateTab('filter', $create);
    }

    public function setCreateTabColumn($create)
    {
        return $this->setCreateTab('column', $create);
    }

    public function setCreateTabGroup($create)
    {
        return $this->setCreateTab('group', $create);
    }

    public function setCreateTabSave($create)
    {
        return $this->setCreateTab('save', $create);
    }

    public function setCreateTab($tabName, $create)
    {
        $this->createTabs[$tabName] = $create;

        return $this;
    }

    public function getCreateTab($tabName)
    {
        if (isset($this->createTabs[$tabName]))
        {
            return $this->createTabs[$tabName];
        }

        return false;
    }

    /**
     * Automatctelly create the filters
     *
     * @return array
     */
    protected function createAutoFilters()
    {
        $dbModel = $this->getDbModel();
        $smartFilter = new \Filter\Smart(null, 'q', \Filter\Text::FILTER_TYPE_ENABLE_SHOW_ALWAYS);
        $columns = $this->getDataSourceOriginal()->getColumns();
        $filters = \Component\Grid\MountFilter::getFilters($columns, $dbModel, $this->filters);
        $filters = array_merge(array($smartFilter), $filters);

        $this->autoFiltersCreated = true;

        return $filters;
    }

    public function addFilter($extraFilter)
    {
        if (!$extraFilter)
        {
            return $this;
        }

        if (is_array($extraFilter))
        {
            foreach ($extraFilter as $filter)
            {
                $filter instanceof \Filter\Text;
                $this->filters[$filter->getFilterName()] = $filter;
            }
        }
        else
        {
            $this->filters[$extraFilter->getFilterName()] = $extraFilter;
        }

        return $this;
    }

    /**
     * Get filter by name
     * @param string $filterName filter name
     * @return  \Filter\Text
     */
    function getFilter($filterName)
    {
        $filters = $this->getFilters();

        if (isset($filters[$filterName]))
        {
            return $filters[$filterName];
        }

        //falback for method to work
        return new \Filter\Text();
    }

    /**
     * Remove an filter
     * @param strring $filterName filter name
     *
     * @return $this
     */
    function removeFilter($filterName)
    {
        $filters = $this->getFilters();

        if (isset($filters[$filterName]))
        {
            unset($filters[$filterName]);
        }

        $this->setFilters($filters);

        return $this;
    }

    /**
     * Return an array with all filter
     *
     * @return array
     */
    function getFilters()
    {
        if (!$this->autoFiltersCreated)
        {
            $this->filters = $this->createAutoFilters();
        }

        return $this->filters;
    }

    /**
     * Define/overwrite all filter
     * @param array $extraFilters array of filter
     * @return $this
     */
    function setFilters($extraFilters)
    {
        if (!is_array($extraFilters))
        {
            $extraFilters = array($extraFilters);
        }

        $this->filters = $extraFilters;

        return $this;
    }

    /**
     * Disable an entery groups os filters
     *
     * @param array $array array of group names
     * @return $this
     */
    public function disableFilterGroup($array)
    {
        $this->setFilters(\Component\Grid\MountFilter::disableGroup($this->getFilters(), $array));

        return $this;
    }

    public function getAllColumns()
    {
        $dbModel = $this->getDbModel();

        if ($dbModel)
        {
            $columnGroup = \DataSource\ColumnConvert::dbToGridAllGrouped($dbModel);
        }
        else
        {
            $page = \View\View::getDom();
            $datasource = $page->getDatasource();
            $columns = $datasource->getColumns();
            $title = $page->getTitle() ? \Component\Grid\GroupHelper::safeName($page->getTitle()) : 'Colunas';

            $columnGroup[$title] = $columns;
        }

        return $columnGroup;
    }

    public function getColumnsGroup()
    {
        if (!$this->columnsGroup)
        {
            $this->columnsGroup = $this->getAllColumns();
        }

        return $this->columnsGroup;
    }

    public function setColumnsGroup($columnsGroup)
    {
        $this->columnsGroup = $columnsGroup;
        return $this;
    }

    public function getColumnsCustomize()
    {
        if (!$this->columnsCustomize)
        {
            $this->columnsCustomize = $this->getAllColumns();
        }

        return $this->columnsCustomize;
    }

    public function setColumnsCustomize($columnsCustomize)
    {
        $this->columnsCustomize = $columnsCustomize;
        return $this;
    }

    public function getAllColumnsForGroup()
    {
        $columns = $this->getAllColumns();

        return $columns;
    }

    /**
     * Make the creation of the grid
     *
     * @return \Component\Grid
     */
    public function onCreate()
    {
        //avoid double creation
        if ($this->isCreated())
        {
            return $this->getContent();
        }

        $tab = new \View\Ext\Tab('tab-holder-search-field');
        $tab->add('tab-list', 'Listagem', '', true, 'list');

        $this->createTabFilter($tab);
        $this->createTabColumn($tab);
        $this->createTabGroup($tab);
        $this->createTabSave($tab);
        $this->setContent($tab);

        $filterSmart = new \Filter\Smart();

        $this->byId('tab-holder-search-fieldHead')->append($filterSmart->getInput());
        $this->byId('tab-list')->append($this->createTable());

        //visualize hidden tab head if only has one tab
        if ($tab->getTabCount() == 1)
        {
            $this->byId('tab-listLabel')->css('visibility', 'hidden');
        }

        \App::addJs("mountExtraFiltersLabel();");

        return $this->content;
    }

    protected function createTabFilter(\View\Ext\Tab $tab)
    {
        $tabItem = null;

        if ($this->getCreateTab('filter'))
        {
            $fields = $this->mountAdvancedFiltersMenu();
            $tabItem = $tab->add('tab-filter', 'Filtros', $fields, true, 'filter');
            //update the filter js
            \App::addJs("$('.filterCondition').change();");
        }

        return $tabItem;
    }

    protected function createTabColumn(\View\Ext\Tab $tab)
    {
        $pageUrl = \View\View::getDom()->getPageUrl();
        $tabItem = null;
        $hasExtraColumns = Request::get('grid-addcolumn-field');

        if ($this->getCreateTab('column'))
        {
            $fields = $hasExtraColumns ? $this->createColumns() : null;
            $tabItem = $tab->add('tab-column', 'Colunas', $fields, null, 'columns');

            //put an ajax link, to only open this part if is needed
            if (!$hasExtraColumns)
            {
                $this->byId('tab-columnLabel')->click("return p('{$pageUrl}/gridGroupCreateColumns');");
            }
        }

        return $tabItem;
    }

    protected function createTabGroup(\View\Ext\Tab $tab)
    {
        $pageUrl = \View\View::getDom()->getPageUrl();
        $tabItem = null;
        $hasGroupments = Request::get('grid-groupby-field');

        if ($this->getCreateTab('group'))
        {
            $fields = $hasGroupments ? $this->createGroupment() : null;
            $tabItem = $tab->add('tab-group', 'Agrupamentos', $fields, true, 'layer-group');

            if (!$hasGroupments && $this->getCreateTab('group'))
            {
                $this->byId('tab-groupLabel')->click("return p('{$pageUrl}/gridGroupGroupment');");
            }
        }

        return $tabItem;
    }

    public function createTabSave(\View\Ext\Tab $tab)
    {
        $tabItem = null;

        if ($this->getCreateTab('save'))
        {
            $tabItem = $tab->add('tab-save', 'Salvar', $this->createBookmarkMenu(), null, 'save');
        }

        return $tabItem;
    }

    public function getCallInterfaceFunctions()
    {
        return $this->callInterfaceFunctions;
    }

    public function setCallInterfaceFunctions($callInterfaceFunctions)
    {
        $this->callInterfaceFunctions = $callInterfaceFunctions;
    }

    protected function createTd(\Component\Grid\Column $column, $index, $item, $tr)
    {
        $dom = \View\View::getDom();
        $td = parent::createTd($column, $index, $item, $tr);

        //from page
        if ($this->getCallInterfaceFunctions() && $dom instanceof \Page\AfterGridCreateCell)
        {
            \View\View::getDom()->afterGridCreateCell($column, $item, $index, $tr, $td);
        }

        //from grid
        if ($this instanceof \Page\AfterGridCreateCell)
        {
            $this->afterGridCreateCell($column, $item, $index, $tr, $td);
        }

        return $td;
    }

    protected function createTr($columns, $index, $item)
    {
        $dom = \View\View::getDom();

        if (!$this->isGrouped())
        {
            //from page
            if ($this->getCallInterfaceFunctions() && $dom instanceof \Page\BeforeGridCreateRow)
            {
                $dom->beforeGridCreateRow($item, $index, NULL);
            }

            //from grid
            if ($this instanceof \Page\BeforeGridCreateRow)
            {
                $this->beforeGridCreateRow($item, $index, null);
            }
        }

        $tr = parent::createTr($columns, $index, $item);

        if (!$this->isGrouped())
        {
            //from page
            if ($this->getCallInterfaceFunctions() && $dom instanceof \Page\AfterGridCreateRow)
            {
                \View\View::getDom()->afterGridCreateRow($item, $index, $tr);
            }

            //from grid
            if ($this instanceof \Page\AfterGridCreateRow)
            {
                $this->afterGridCreateRow($item, $index, $tr);
            }
        }

        return $tr;
    }

    public function getSearchButton($id = 'buscar')
    {
        $params['orderBy'] = Request::get('orderBy');
        $params['orderWay'] = Request::get('orderWay');
        $params = http_build_query($params);

        $url = 'g(\'' . $this->getLink(NULL, NULL, NULL, false) . '\',\'' . $params . '\'+ \'&\' + $(\'.content\').serialize())';

        $btnSearch = new \View\Ext\Button($id, 'search', 'Buscar', $url, 'primary', 'Clique para pesquisa');
        $btnSearch->setTitle('Buscar');

        //$holder = new \View\Div(null, $btnSearch, 'column-12 btn-search-holder');

        return $btnSearch;
    }

    /**
     * Mount the input of search
     *
     * @param string $idQuestion
     * @param string $idBtn
     * @return \View\Input
     */
    protected function getInput($idQuestion, $idBtn)
    {
        $search = new \View\Input($idQuestion, \View\Input::TYPE_SEARCH, Request::get($idQuestion));

        $search->setAttribute('placeholder', 'Pesquisar...')
                ->setClass('search fullWidth')
                ->setValue(Request::get($idQuestion))
                ->setTitle('Digite o conteúdo a buscar...')
                ->onPressEnter('$("#' . $idBtn . '").click();');

        $fields = array();
        $fields[] = new \View\Label(null, 'q', 'Pesquisar', 'filterLabel');
        $fields[] = $search;

        return new \View\Div('main-search', $fields, 'filterField');
    }

    public function getDbModel()
    {
        $dom = \View\View::getDom();
        $dbModel = null;

        //detected if page has get model method
        if (method_exists($dom, 'getModel'))
        {
            $dbModel = $dom->getModel();
        }

        return $dbModel;
    }

    protected static function organizeByGroup($filters)
    {
        $groups = [];

        if (is_array($filters))
        {
            foreach ($filters as $filter)
            {
                $filter instanceof \Filter\Text;
                $groups[$filter->getFilterGroup()][$filter->getFilterLabel()] = $filter;
            }
        }

        ksort($groups);

        foreach ($groups as $groupName => $group)
        {
            ksort($group);
            $groups[$groupName] = $group;
        }

        return $groups;
    }

    /**
     * Mount the advance filters
     *
     * @param string $idQuestion
     * @param string $idBtn
     * @return \View\Div
     */
    protected function mountAdvancedFiltersMenu()
    {
        $groups = self::organizeByGroup($this->getFilters());
        $menuComplex = count($groups) > 2;
        $optGroup = [];

        foreach ($groups as $groupName => $filters)
        {
            $options = [];

            foreach ($filters as $filter)
            {
                $filter instanceof \Filter\Text;

                //avoid disabled filters
                if ($filter->getFilterType() . '' == \Filter\Text::FILTER_TYPE_DISABLE . '')
                {
                    continue;
                }

                if ($filter instanceof \Filter\Smart)
                {
                    continue;
                }

                $options[] = new \View\Option($filter->getFilterName(), $filter->getFilterLabel());
            }

            $groupNameFile = null;

            if ($groupName && $menuComplex)
            {
                $groupLabel = $groupName;

                //change visual grouplabel, to show correctely in menu
                if ($groupName == \Filter\Text::GROUP_MAIN)
                {
                    $groupLabel = $this->getDbModel()->getLabel();
                }

                $groupNameFile = \Type\Text::get($groupName)->toFile('-');

                $optGroup[] = new \View\OptGroup($groupNameFile, $groupLabel, $options);
                $options = [];
            }

            //rare case with only one model
            if (count($options) > 0)
            {
                $title = $this->getDbModel() ? $this->getDbModel()->getLabel() : 'Filtros';
                $optGroup[] = new \View\OptGroup($groupNameFile, $title, $options);
            }
        }

        $content[] = new \View\Label(null, null, 'Filtrar por', 'field-label');
        $select = new \View\Select('advancedFiltersList', $optGroup, null, 'column-12 column-list-holder');
        $select->setAttribute('multiple', 'multiple');

        $content[] = new \View\Div(null, $select, 'column-12 ');
        $content[] = $btn = new \View\Ext\Button('btnAddAvdFilter', 'plus', 'Adicionar filtro', 'addAdvancedFilter');
        $btn->css('border', 'none');
        $content[] = $this->getSearchButton();

        $right = [];
        $right[] = new \View\Label(null, null, 'Filtros aplicados', 'field-label');
        $right[] = new \View\Div('filters-holder', $this->createFilterFieldsNeeded());

        $result[] = new \View\Div('tab-filters-left', $content, 'tab-filters-left column-p-6');
        $result[] = new \View\Div('tab-filters-right', $right, 'tab-filters-right column-p-6');

        //remove empty option
        \App::addJs("$('#advancedFiltersList #select-null-option').remove();");

        return $result;
    }

    public function createColumns()
    {
        $columns = $this->getColumnsCustomize();
        $left[] = new \View\Label(null, null, 'Adicionar colunas', 'field-label');

        $select = new \View\Select('addColumn', $this->createColumnOptions($columns), null, 'column-12 column-list-holder');
        $select->setAttribute('multiple', 'multiple');

        $left[] = new \View\Div(null, $select, 'column-12');

        $left[] = $btn = new \View\Ext\Button('btnAddColumn', 'plus', 'Adicionar coluna', 'gridGroupAddColumn');
        $btn->css('border', 'none');
        $left[] = $this->getSearchButton();

        $right = [];
        $right[] = new \View\Label(null, null, 'Colunas aplicadas', 'field-label');

        $content[] = new \View\Div('columns-definition', $left, 'column-p-6');
        $content[] = new \View\Div('columns-holder', $right, 'columns-holder column-p-6');

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
            $mainColumns = $this->getDataSourceOriginal()->getColumns();

            foreach ($mainColumns as $column)
            {
                if ($column->getRender())
                {
                    $elements[] = self::createFieldColumn($column);
                }
            }
        }

        $this->byId('columns-holder')->append($elements);
        //remove empty option
        \App::addJs("setTimeout(function(){ $('.column-list-holder #select-null-option').remove(); }, 200);");

        return $content;
    }

    protected function createBookmarkMenu()
    {
        $pageUrl = \View\View::getDom()->getPageUrl();
        $saveList = new \Filter\SavedList();
        $json = $saveList->getObject();
        $inner = [];

        $url = new \View\A('search-bookmark-reset', 'Voltar para pesquisa normal/padrão', $pageUrl);
        $url->setAjax(false);
        $div = new \View\Div(null, $url, 'column-12 grid-addcolumn-field');
        $inner[] = $div;
        $buttons = null;

        if (isIterable($json))
        {
            foreach ($json as $id => $item)
            {
                if ($item->page != $pageUrl)
                {
                    continue;
                }

                $linkUrl = $item->page . '/?' . $item->url . '&search-title=' . $item->title;
                $url = new \View\A('search-bookmark-' . $item->id, $item->title, $linkUrl);
                $url->setAjax(false);

                $removeUrl = "return p(\"$pageUrl/deleteListItem/?savedList=$id\");";
                $removeIcon = new \View\Ext\Icon('trash', 'remove-item-' . $id, $removeUrl, 'trashFilter');

                $div = new \View\Div(null, [$url, $removeIcon], 'column-12 grid-addcolumn-field');

                $inner[] = $div;

                $buttons[] = $btn = new \View\Ext\LinkButton('btn-search-bookmak-' . $item->id, null, $item->title, $linkUrl, 'small btn-search-bookmark');
                $btn->setAjax(false);
            }
        }

        if ($buttons)
        {
            $buttonsStart[] = $btn = new \View\Ext\LinkButton('btn-search-bookmak-reset', null, 'Padrão', $pageUrl, 'small btn-search-bookmark');
            $btn->setAjax(false);

            $this->byId('tab-list')->html(new \View\Div('btn-search-bookmark-holder', array_merge($buttonsStart, $buttons)));
        }

        //empty
        if (count($inner) == 0)
        {
            $inner[] = new \View\Div(null, 'Nenhuma pesquisa salva ainda', 'column-12 grid-addcolumn-field');
        }

        $content[] = new \View\Label(null, null, 'Pesquisas salvas', 'field-label');
        $content[] = new \View\Div('searchSaveList', $inner, 'column-6 grid-savedlist-holder');

        $url = "p('{$pageUrl}/saveListItem',$('.content').serialize());";
        $btn = new \View\Ext\Button('btnsaveListItem', 'plus', 'Salvar os filtros da pesquisa atual', $url);
        $btn->css('border', 'none');

        $content[] = new \View\Div(null, $btn, 'column-12');

        return new \View\Div('bookmark-holder', $content, 'column-p-12');
    }

    protected function createFilterFieldsNeeded()
    {
        $filterContent = array();

        $header = [];
        $inner = [];
        $inner[] = new \View\Div(null, 'Condição', 'filterCondition');
        $inner[] = new \View\Div(null, 'Filtro', 'filterInput');
        $header[] = new \View\Div(null, 'Coluna', 'filterLabel');
        $header[] = new \View\Div(null, $inner, 'filterBase');

        $filterContent[] = new \View\Div('filter-field-header', $header, 'filter-field-header filterField');

        if (is_array($this->filters))
        {
            foreach ($this->filters as $filter)
            {
                if ($filter instanceof \Filter\Smart)
                {
                    continue;
                }

                $filterNameCondition = $filter->getFilterName() . 'Condition';
                $filterNameValue = $filter->getFilterName() . 'Value';

                $filterCondValues = Request::get($filterNameCondition);
                $filterNameValues = Request::get($filterNameValue);
                $hasCondValues = is_array($filterCondValues) || is_string($filterCondValues);
                $hasFilterValues = is_array($filterNameValues) || is_string($filterNameValues);

                $needCreation = $hasCondValues || $hasFilterValues;

                //create the filter if not ajax (reload (F5))
                if ($needCreation && $filter->getFilterType() . '' != \Filter\Text::FILTER_TYPE_ENABLE_SHOW_ALWAYS . '')
                {
                    $filterContent[] = $filter->getInput();
                }

                //fixed filters
                if ($filter->getFilterType() . '' == \Filter\Text::FILTER_TYPE_ENABLE_SHOW_ALWAYS . '')
                {
                    $filterContent[] = $filter->getInput();
                }
            }
        }

        return $filterContent;
    }

    /**
     * Mount collumn list options for group
     *
     * @param array $columnGroup \Component\Grid\Column column list list
     * @return array array of stdClass
     */
    public function createColumnOptions($columnGroup)
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

                //we have weird duplicated names in some models
                $label = $column->getGroupName() . '_' . $column->getLabel();

                if (isset($options[$label]))
                {
                    $label .= '_';
                }

                $options[$label] = $option;
            }

            ksort($options);

            $opt->html($options);
        }

        return $optgroup;
    }

    public function createGroupment()
    {
        $columns = $this->getColumnsGroup();
        $left[] = new \View\Label(null, null, 'Colunas do agrupamento', 'field-label');
        $left[] = $select = new \View\Select('gridGroupBy', $this->createColumnOptions($columns), null, 'column-12 column-list-holder');
        $select->setAttribute('multiple', 'mulitple');

        $left[] = $btn = new \View\Ext\Button('btnAddGroup', 'plus', 'Coluna agrupamento', 'gridGroupAddGroup');
        $left[] = $this->getSearchButton('buscarGroup');

        $right[] = new \View\Label(null, null, 'Agrupamentos', 'field-label');
        $right[] = new \View\Select('gridAggrBy', $this->createColumnOptions($columns), null, 'column-6');
        $right[] = new \View\Select('gridAggrMethods', \Component\Grid\GroupHelper::listAggrMethods(), null, 'column-6');

        $right[] = $btn = new \View\Ext\Button('btnAddAggr', 'plus', 'Adicionar agrupamento', 'gridGroupAddAggr');
        $right[] = $leftHolder = new \View\Div('leftHolder', null, 'column-12 grid-group-by-left-holder');
        $right[] = $rightHolder = new \View\Div('rightHolder', null, 'column-12 grid-group-by-right-holder');

        $content[] = new \View\Div('left', $left, 'column-p-6');
        $content[] = new \View\Div('right', $right, 'column-p-6');

        $content[] = $this->createLoadedInputs();

        //remove empty option
        \App::addJs("setTimeout(function(){ $('.column-list-holder #select-null-option').remove(); }, 200);");

        return $content;
    }

    public function createLoadedInputs()
    {
        $gridGroupBy = Request::get('grid-groupby-field');
        $gridAggrBy = Request::get('grid-aggrby-field');
        $groupColumns = $this->getAllColumns();

        $elements = [];

        if (is_array($gridGroupBy))
        {
            foreach ($gridGroupBy as $groupBy)
            {
                $explode = explode('.', $groupBy);
                $columnGroup = $explode[0];
                $columnName = $explode[1];
                $column = $groupColumns[$columnGroup][$columnName];

                if ($column)
                {
                    $elements[] = $this->createFieldGroupBy($column);
                }
            }
        }

        $this->byId('leftHolder')->append($elements);
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

                if ($column)
                {
                    $elements[] = self::createFieldAggr($column, $method);
                }
            }
        }

        $this->byId('rightHolder')->append($elements);
    }

    /**
     * Crete a group by filter
     *
     * @param \Component\Grid\Column $column
     * @return \View\Div
     */
    public function createFieldGroupBy(\Component\Grid\Column $column)
    {
        $columName = \Component\Grid\GroupHelper::safeName($column->getGroupName()) . '.' . $column->getName();
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
        $columName = \Component\Grid\GroupHelper::safeName($column->getGroupName()) . '.' . $column->getName();
        $label = $column->getGroupName() . ' - ' . $column->getLabel() . ' - <strong>' . $methods[$method] . '</strong>';
        $idField = 'grid-aggrby-field-' . $columName;
        $value = $method . '--' . $columName;

        $content = [];
        $content[] = new \View\Input('grid-aggrby-field[' . $value . ']', 'hidden', $value);

        $content[] = $label;
        $content[] = $btnRemove = new \View\Ext\Icon('trash', null, "$(this).parent().remove();", 'trashFilter');

        $div = new \View\Div($idField, $content, 'column-12 grid-addcolumn-field grid-addcolumn-field-group');

        return $div;
    }

    /**
     * Action called when user add a group
     * @param \Page\Page $page
     * @throws \UserException
     */
    public function popupAddGroup()
    {
        $gridGroupBy = Request::get('gridGroupBy');

        if (!$gridGroupBy)
        {
            throw new \UserException('Selecione uma coluna!');
        }

        $explode = explode('.', $gridGroupBy);
        $columnGroup = $explode[0];
        $columnName = $explode[1];
        $groupColumns = $this->getAllColumns();
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

        $div = $this->createFieldGroupBy($column);

        $this->byId('leftHolder')->append($div);
        $this->byId('gridGroupBy')->val('');
    }

    public function popupAddAggr()
    {
        $gridAggrBy = Request::get('gridAggrBy');
        $method = Request::get('gridAggrMethods');

        if (!$gridAggrBy || !$method)
        {
            throw new \UserException('Selecione ambos parametros!');
        }

        $page = \View\View::getDom();
        $grid = $page->getGrid();
        $columns = $grid->getDataSourceOriginal()->getColumns();
        $explode = explode('.', $gridAggrBy);
        $columnGroup = $explode[0];
        $columnName = $explode[1];
        $groupColumns = $this->getAllColumns();
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

        $this->byId('rightHolder')->append($div);
        $this->byId('gridAggrBy')->val('');
        $this->byId('gridAggrMethods')->val('');
    }

    /**
     * Action called when user is adding a column
     *
     * @param \Page\Page $page
     * @throws \UserException
     */
    public function gridGroupAddColumn()
    {
        $addColumn = Request::get('addColumn');

        if (!$addColumn)
        {
            throw new \UserException('Selecione uma coluna!');
        }

        $explode = explode('.', $addColumn);
        $columnGroup = $explode[0];
        $columnName = $explode[1];
        $groupColumns = $this->getAllColumns();

        if (!isset($groupColumns[$columnGroup][$columnName]))
        {
            throw new \UserException('Impossível encontrar coluna ' . $addColumn);
        }

        $column = $groupColumns[$columnGroup][$columnName];

        $selecionados = Request::get('grid-addcolumn-field');

        if (isset($selecionados[$addColumn]))
        {
            throw new \UserException('Campo \'' . $column->getGroupName() . ' - ' . $column->getLabel() . '\' já adicionado ao agrupamento.');
        }

        $div = self::createFieldColumn($column);

        $this->byId('columns-holder')->append($div);
        $this->byId('addColumn')->val('');
    }

    /**
     * Create a column field
     *
     * @param \Component\Grid\Column $column
     * @return \View\Div
     */
    public static function createFieldColumn(\Component\Grid\Column $column)
    {
        $columName = \Component\Grid\GroupHelper::safeName($column->getGroupName()) . '.' . $column->getName();
        $label = $column->getLabel();

        if ($column->getGroupName())
        {
            $label = $column->getGroupName() . ' - ' . $label;
        }

        $idField = 'grid-addcolumn-field-' . $columName;
        $value = $columName;

        $content = [];
        $content[] = new \View\Input('grid-addcolumn-field[' . $value . ']', 'hidden', $value);

        if ($column instanceof \Component\Grid\CheckColumn)
        {
            $label = $column->getGroupName() . ' - Checagem';
        }

        $content[] = $label;

        $isImutable = $column instanceof \Component\Grid\PkColumnEdit || $column instanceof \Component\Grid\CheckColumn;

        if (!$isImutable)
        {
            $content[] = new \View\Ext\Icon('trash', null, "return gridAddColumnRemove(this)", 'grid-addcolumn-icon');
            $content[] = new \View\Ext\Icon('arrow-down', null, "return gridAddColumnDown(this)", 'grid-addcolumn-icon');
            $content[] = new \View\Ext\Icon('arrow-up', null, "return gridAddColumnUp(this)", 'grid-addcolumn-icon');
        }

        $div = new \View\Div($idField, $content, 'grid-addcolumn-field column-12');

        return $div;
    }

}
