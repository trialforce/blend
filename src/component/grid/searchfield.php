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

    function getExtraFilters()
    {
        if (!$this->autoFiltersCreated)
        {
            $this->filters = $this->createAutoFilters();
        }

        return $this->filters;
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

    public function onCreate()
    {
        //avoid double creation
        if ($this->isCreated())
        {
            return $this->getContent();
        }

        $innerHtml[] = $this->mountAdvancedFiltersMenu();
        $innerHtml[] = $this->createBookmarkMenu();
        $innerHtml[] = new \View\Div('containerFiltros', $this->createFilterFieldsNeeded(), 'clearfix');
        $innerHtml[] = $this->getSearchButton();

        $views[] = new \View\Div('containerHead', $innerHtml, 'input-append');

        $div = new \View\Div('searchHead', $views, 'hide-in-mobile');

        $this->setContent($div);

        //update the filter js
        \App::addJs("$('.filterCondition').change();");
        //order the list in alpha
        \App::addJs("sortList('#fm-filters');");

        return $div;
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

    /**
     * Mount the advance filters
     *
     * @param string $idQuestion
     * @param string $idBtn
     * @return \View\Div
     */
    protected function mountAdvancedFiltersMenu()
    {
        $pageUrl = \View\View::getDom()->getPageUrl();

        $icon = new \View\Ext\Icon('filter filter-menu', 'advanced-filter', '$(\'#fm-filters\').toggle(\'fast\');');
        $fMenu = new \View\Blend\FloatingMenu('fm-filters');
        $icon->append($fMenu->hide());

        $filters = $this->getExtraFilters();

        if (is_array($filters))
        {
            foreach ($filters as $filter)
            {
                $filter instanceof \Filter\Text;
                $url = "p('$pageUrl/addAdvancedFilter/{$filter->getFilterName()}');";
                $fMenu->addItem('advanced-filter-item-' . $filter->getFilterName(), null, $filter->getFilterLabel(), $url);
            }
        }

        $result[] = $icon;

        return $result;
    }

    protected function createBookmarkMenu()
    {
        $pageUrl = \View\View::getDom()->getPageUrl();

        $icon = new \View\Ext\Icon('thumbtack filter-menu');
        $icon->setId('bookmark-filter')->click('$(\'#fm-bookmark\').toggle(\'fast\');');

        $menu = new \View\Blend\FloatingMenu('fm-bookmark');
        $icon->append($menu->hide());

        $saveList = new \Filter\SavedList();
        $json = $saveList->getObject();

        if (isIterable($json))
        {
            foreach ($json as $id => $item)
            {
                if ($item->page == $pageUrl)
                {
                    $content = array();
                    $span = new \View\Span(null, $item->title);
                    $span->click("window.location = (\"$item->page/?$item->url\");");

                    $removeUrl = "return p(\"$pageUrl/deleteListItem/?savedList=$id\");";
                    $removeIcon = new \View\Ext\Icon('trash', 'remove-item-' . $id, $removeUrl);

                    $content[] = $span;
                    $content[] = $removeIcon;

                    $menu->addItem('save-item-' . $id, null, $content, null);
                }
            }
        }

        $content = array();
        $span = new \View\Span(null, 'Salvar os filtros da pesquisa atual..');
        $icon2 = new \View\Ext\Icon('save', 'icon-save-new-item');

        $content[] = $span;
        $content[] = $icon2;

        $url = "p('{$pageUrl}/saveListItem',$('.content').serialize());";
        $menu->addItem('save-item-new', null, $content, $url);

        return $icon;
    }

    /**
     * Create the fielters neeed when update grid
     * @param array $filters the array of filter
     * @return array the array of fields
     */
    protected function createFilterFieldsNeeded()
    {
        //$filterContent = array($this->getInput('q', 'buscar'));
        $filterContent = array();

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

                $needCreation = $hasCondValues || $hasFilterValues || $filter->getFilterType() . '' == \Filter\Text::FILTER_TYPE_ENABLE_SHOW_ALWAYS;

                //create the filter if not ajax (reload (F5))
                if ($needCreation)
                {
                    $filterContent[] = $filter->getInput();
                }
            }
        }

        return $filterContent;
    }

}
