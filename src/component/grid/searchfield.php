<?php

namespace Component\Grid;

use DataHandle\Request;

/**
 * Search field for grid
 */
class SearchField extends \Component\Component
{

    /**
     * Alias to grid
     * @var \Component\Grid\SearchGrid
     */
    protected $grid;

    /**
     * Extra filters
     * @var array
     */
    protected $filters = array();

    /**
     * auto filters created
     * @var bool
     */
    protected $autoFiltersCreated = false;

    /**
     * Construct the Search field.
     *
     * @param \View\Page $dom
     * @param \Component\Grid\Grid $grid
     * @param string $class
     */
    public function __construct($grid = NULL)
    {
        $this->setGrid($grid);

        parent::__construct('searchHead', null);
    }

    public function onCreate()
    {
        //avoid double creation
        if ($this->isCreated())
        {
            return $this->getContent();
        }

        $icon = new \View\Ext\Icon('filter filter-menu blend-floating-menu-holder', 'advanced-filter', "popup('show', '#popupSearchField');");
        $icon->append(new \View\Span(null, 'Filtros', 'advanced-filter-span'));
        $innerHtml[] = $icon;
        $this->createPopup();
        $innerHtml[] = new \View\Div('containerFiltros', $this->createFixedFilters(), 'clearfix');
        $innerHtml[] = $this->getSearchButton();

        $views[] = new \View\Div('containerHead', $innerHtml, 'input-append');

        $div = new \View\Div('searchHead', $views, 'hide-in-mobile');

        $this->setContent($div);

        //update the filter js
        \App::addJs("$('.filterCondition').change();");
        \App::addJs("mountExtraFiltersLabel();");

        return $div;
    }

    function createPopup()
    {
        $hasExtraColumns = Request::get('grid-addcolumn-field');

        $tab = new \View\Ext\Tab('tab-holder-search-field');
        $tab->add('tab-filter', 'Filtros', $this->mountAdvancedFiltersMenu());
        $tab->add('tab-column', 'Colunas', $hasExtraColumns ? \Component\Grid\GroupHelper::createColumns() : null);
        $tab->add('tab-group', 'Agrupamentos', \Component\Grid\GroupHelper::createContent());
        $tab->add('tab-save', 'Salvar', $this->createBookmarkMenu());

        $urlClear = 'popup(\'close\', \'#popupSearchField\');  g(\'' . $this->grid->getLink(NULL, NULL, NULL, false) . '\',\'\')';

        $buttons[] = new \View\Ext\Button('btn-search-clear', 'undo', '', $urlClear, 'btn-search-clear icon-only', 'Clique para limpar a busca e iniciar uma nova');
        $buttons[] = new \View\Ext\Button('okPopup', 'search', 'Buscar', "return gridClosePopupAndMakeSearch();", '', 'Clique para efetuar sua busca');

        $popup = new \View\Blend\Popup('popupSearchField', 'Definições de pesquisa', $tab, $buttons, 'form');
        $popup->setIcon('search');
        $popup->generateTitle('close');

        $pageUrl = \View\View::getDom()->getPageUrl();

        //put an ajax link, to only open this part if is needed
        if (!$hasExtraColumns)
        {
            $this->byId('tab-columnLabel')->click("return p('{$pageUrl}/gridGroupCreateColumns');");
        }

        return $popup;
    }

    function getExtraFilters()
    {
        if (!$this->autoFiltersCreated)
        {
            $this->filters = $this->createAutoFilters();
        }

        return $this->filters;
    }

    /**
     * Get Filter
     *
     * @param string $filterName filter name
     * @return \Filter\Text
     */
    public function getExtraFilter($filterName)
    {
        $filters = $this->getExtraFilters();

        if (isset($filters[$filterName]))
        {
            return $filters[$filterName];
        }

        return null;
    }

    protected function createAutoFilters()
    {
        $dbModel = $this->getDbModel();
        $grid = $this->getGrid();
        $smartFilter = new \Filter\Smart(null, 'q', \Filter\Text::FILTER_TYPE_ENABLE_SHOW_ALWAYS);
        $filters = \Component\Grid\MountFilter::getFilters($grid->getColumns(), $dbModel, $this->filters);
        $filters = array_merge(array($smartFilter), $filters);
        $this->autoFiltersCreated = true;
        return $filters;
    }

    function setExtraFilters($extraFilters)
    {
        if (!is_array($extraFilters))
        {
            $extraFilters = array($extraFilters);
        }

        $this->filters = $extraFilters;
    }

    function addExtraFilter($extraFilter)
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

    public function getGrid()
    {
        return $this->grid;
    }

    public function setGrid($grid)
    {
        $this->grid = $grid;
        return $this;
    }

    public function getSearchButton()
    {
        $params['orderBy'] = Request::get('orderBy');
        $params['orderWay'] = Request::get('orderWay');
        $params = http_build_query($params);

        $url = 'g(\'' . $this->grid->getLink(NULL, NULL, NULL, false) . '\',\'' . $params . '\'+ \'&\' + $(\'.content\').serialize())';

        $btnSearch = new \View\Ext\Button('buscar', 'search', 'Buscar', $url, '', 'Clique para pesquisa');
        $btnSearch->setTitle('Buscar');

        $content[] = $btnSearch;
        $content[] = $filtersTooltip = new \View\Div('filters-tooltip', null, 'filters-tooltip');
        $filtersTooltip->click("popup('show', '#popupSearchField');");

        $holder = new \View\Div('btn-search-holder', $content);

        return $holder;
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

    protected function getDbModel()
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
        $groups = self::organizeByGroup($this->getExtraFilters());
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

                //don't add fixed filter to menu
                if ($filter->getFilterType() . '' == \Filter\Text::FILTER_TYPE_ENABLE_SHOW_ALWAYS . '')
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
                $optGroup[] = new \View\OptGroup($groupNameFile, $this->getDbModel()->getLabel(), $options);
            }
        }

        $content[] = new \View\H3(null, 'Filtrar por');
        $select = new \View\Select('advancedFiltersList', $optGroup, null, 'column-6');
        $content[] = new \View\Div(null, $select, 'column-12');
        $content[] = $btn = new \View\Ext\Button('btnAddAvdFilter', 'plus', 'Adicionar filtro', 'addAdvancedFilter', 'clean small');
        $btn->css('border', 'none');

        $result[] = new \View\Div('tab-filters-left', $content, 'tab-filters-left column-p-12');
        $result[] = new \View\Div('tab-filters-right', $this->createFilterFieldsNeeded(), 'tab-filters-right column-p-12');

        return $result;
    }

    protected function createBookmarkMenu()
    {
        $pageUrl = \View\View::getDom()->getPageUrl();
        $saveList = new \Filter\SavedList();
        $json = $saveList->getObject();
        $inner = [];

        if (isIterable($json))
        {
            foreach ($json as $id => $item)
            {
                if ($item->page != $pageUrl)
                {
                    continue;
                }

                $removeUrl = "return p(\"$pageUrl/deleteListItem/?savedList=$id\");";
                $removeIcon = new \View\Ext\Icon('trash', 'remove-item-' . $id, $removeUrl, 'trashFilter');

                $div = new \View\Div(null, [$item->title, $removeIcon], 'column-12 grid-addcolumn-field');
                $div->click("window.location = (\"$item->page/?$item->url\");");

                $inner[] = $div;
            }
        }

        //empty
        if (count($inner) == 0)
        {
            $inner[] = new \View\Div(null, 'Nenhuma pesquisa salva ainda', 'column-12 grid-addcolumn-field');
        }

        $content[] = new \View\H3(null, 'Pesquisas salvas:');
        $content[] = new \View\Div('searchSaveList', $inner, 'column-6 grid-savedlist-holder');

        $url = "p('{$pageUrl}/saveListItem',$('.content').serialize());";
        $btn = new \View\Ext\Button('btnAddAvdFilter', 'plus', 'Salvar os filtros da pesquisa atual', $url, 'clean small');
        $btn->css('border', 'none');

        $content[] = new \View\Div(null, $btn, 'column-12');

        return new \View\Div(null, $content, 'column-p-12');
    }

    /**
     * Create the fielters neeed when update grid
     * @param array $filters the array of filter
     * @return array the array of fields
     */
    protected function createFixedFilters()
    {
        //$filterContent = array($this->getInput('q', 'buscar'));
        $filterContent = array();

        if (is_array($this->filters))
        {
            foreach ($this->filters as $filter)
            {
                if ($filter->getFilterType() . '' == \Filter\Text::FILTER_TYPE_ENABLE_SHOW_ALWAYS . '')
                {
                    $filterContent[] = $filter->getInput();
                }
            }
        }

        return $filterContent;
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
            }
        }

        return $filterContent;
    }

}
