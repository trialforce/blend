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

        $tab = $this->createTab();
        //$views[] = new \View\Div('containerHead', null, 'input-append');
        //$div = new \View\Div('searchHead', $views, 'hide-in-mobile');

        $this->setContent($tab);

        //update the filter js
        \App::addJs("$('.filterCondition').change();");
        \App::addJs("mountExtraFiltersLabel();");

        return $tab;
    }

    function createTab()
    {
        $hasExtraColumns = Request::get('grid-addcolumn-field');
        $hasGroupments = Request::get('grid-groupby-field');

        $tab = new \View\Ext\Tab('tab-holder-search-field');
        $tab->add('tab-list', 'Listagem', '');
        $tab->add('tab-filter', 'Filtros', $this->mountAdvancedFiltersMenu());
        $tab->add('tab-column', 'Colunas', $hasExtraColumns ? \Component\Grid\GroupHelper::createColumns() : null);
        $tab->add('tab-group', 'Agrupamentos', $hasGroupments ? \Component\Grid\GroupHelper::createContent() : null);
        $tab->add('tab-save', 'Salvar', $this->createBookmarkMenu());

        $pageUrl = \View\View::getDom()->getPageUrl();

        //put an ajax link, to only open this part if is needed
        if (!$hasExtraColumns)
        {
            $this->byId('tab-columnLabel')->click("return p('{$pageUrl}/gridGroupCreateColumns');");
        }

        if (!$hasGroupments)
        {
            $this->byId('tab-groupLabel')->click("return p('{$pageUrl}/gridGroupGroupment');");
        }

        $filterSmart = new \Filter\Smart();

        $this->byId('tab-holder-search-fieldHead')->append($filterSmart->getInput());

        return $tab;
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

    /**
     * Remove one filter
     * @param string $filterName filter name
     * @return $this
     */
    public function removeExtraFilter($filterName)
    {
        $filters = $this->getExtraFilters();

        if (isset($filters[$filterName]))
        {
            unset($filters[$filterName]);
        }

        $this->setExtraFilters($filters);

        return $this;
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

        //$content[] = $btnSearch;
        //$content[] = $filtersTooltip = new \View\Div('filters-tooltip', null, 'filters-tooltip');
        //$filtersTooltip->click("popup('show', '#popupSearchField');");
        $holder = new \View\Div(null, $btnSearch, 'column-p-12 btn-search-holder');

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
                $title = $this->getDbModel() ? $this->getDbModel()->getLabel() : 'Filtros';
                $optGroup[] = new \View\OptGroup($groupNameFile, $title, $options);
            }
        }

        $content[] = new \View\Label(null, null, 'Filtrar por', 'field-label');
        $select = new \View\Select('advancedFiltersList', $optGroup, null, 'column-6');
        $content[] = new \View\Div(null, $select, 'column-12');
        $content[] = $btn = new \View\Ext\Button('btnAddAvdFilter', 'plus', 'Adicionar filtro', 'addAdvancedFilter');
        $btn->css('border', 'none');

        $result[] = new \View\Div('tab-filters-left', $content, 'tab-filters-left column-p-12');
        $result[] = new \View\Div('tab-filters-right', $this->createFilterFieldsNeeded(), 'tab-filters-right column-p-12');
        $result[] = $this->getSearchButton();

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

                $url = new \View\A('search-bookmark-' . $item->id, $item->title, $item->page . '/?' . $item->url);
                $url->setAjax(false);

                $removeUrl = "return p(\"$pageUrl/deleteListItem/?savedList=$id\");";
                $removeIcon = new \View\Ext\Icon('trash', 'remove-item-' . $id, $removeUrl, 'trashFilter');

                $div = new \View\Div(null, [$url, $removeIcon], 'column-12 grid-addcolumn-field');
                //$div->click("window.location = (\"$item->page/?$item->url\");");

                $inner[] = $div;
            }
        }

        //empty
        if (count($inner) == 0)
        {
            $inner[] = new \View\Div(null, 'Nenhuma pesquisa salva ainda', 'column-12 grid-addcolumn-field');
        }

        $content[] = new \View\Label(null, null, 'Pesquisas salvas', 'field-label');
        $content[] = new \View\Div('searchSaveList', $inner, 'column-6 grid-savedlist-holder');

        $url = "p('{$pageUrl}/saveListItem',$('.content').serialize());";
        $btn = new \View\Ext\Button('btnAddAvdFilter', 'plus', 'Salvar os filtros da pesquisa atual', $url);
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

}
