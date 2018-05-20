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
    protected $extraFilters;

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
        return $this->extraFilters;
    }

    function setExtraFilters($extraFilters)
    {
        if (!is_array($extraFilters))
        {
            $extraFilters = array($extraFilters);
        }

        $this->extraFilters = $extraFilters;
    }

    function addExtraFilter($extraFilter)
    {
        $this->extraFilters[] = $extraFilter;
    }

    public function onCreate()
    {
        //avoid double creation
        if ($this->isCreated())
        {
            return $this->getContent();
        }

        $innerHtml[] = $this->getInput('q', 'buscar');

        if ($this->extraFilters)
        {
            $innerHtml[] = $this->extraFilters;
        }

        $innerHtml[] = $this->getSearchButton();
        $innerHtml[] = $this->getAdvancedFilters();

        $views[] = new \View\Div('containerHead', $innerHtml, 'input-append');

        $div = new \View\Div('searchHead', $views);

        $this->setContent($div);

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

        $url = 'g(\'' . $this->grid->getLink(NULL, NULL, NULL, false) . '\',\'' . $params . '\'+ \'&\' + $(\'form\').serialize())';

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
                ->setClass('search span3')
                ->setValue(Request::get($idQuestion))
                ->setTitle('Digite o conteúdo a buscar...')
                ->onPressEnter('$("#' . $idBtn . '").click();');

        return $search;
    }

    /**
     * Mount the advance filters
     *
     * @param string $idQuestion
     * @param string $idBtn
     * @return \View\Div
     */
    protected function getAdvancedFilters()
    {
        $filterContent = NULL;
        $grid = $this->getGrid();
        $dom = \View\View::getDom();
        $dbModel = null;

        //detected if page has get model method
        if (method_exists($dom, 'getModel'))
        {
            $dbModel = $dom->getModel();
        }

        //TODO verify allready created filters
        $filters = \Component\Grid\MountFilter::getFilters($grid->getColumns(), $dbModel);

        $result[] = $select = new \View\Select('selectFilters', NULL, NULL, 'selectFilters btn add');
        $select->change("var id = $(this).val(); $('#'+id+'Filter').show().find('input, select').removeAttr('disabled'); $('#'+id+'Value').focus()");
        $select->change('addAdvancedFilter')->addOption('', 'Filtro avançado');

        $gridGroup = \DataHandle\Config::get('gridGroup');

        if ($gridGroup)
        {
            $result[] = $selectGroup = new \View\Select('selectGroups', NULL, NULL, 'selectFilters btn add');
            $selectGroup->change('addSearchGroup');
            $selectGroup->addOption('', 'Agrupamento');
        }

        if (is_array($filters))
        {
            foreach ($filters as $filter)
            {
                //filter part
                $select->addOption($filter->getFilterName(), $filter->getFilterLabel());
                $filterNameCondition = $filter->getFilterName() . 'Condition';
                $filterNameValue = $filter->getFilterName() . 'Value';

                //create the filter if not ajax (reload (F5))
                if (Request::get($filterNameCondition) || Request::get($filterNameValue) || Request::get($filterNameValue) === '0' || $filter->getFilterType() . '' == '2')
                {
                    $filterContent[] = $filter->getInput()->append(\Page\Page::getCloseFilterButton());
                }

                //group part
                if ($gridGroup)
                {
                    $selectGroup->addOption($filter->getFilterName(), $filter->getFilterLabel());
                }
            }
        }

        $groupType = NULL;

        //create groups views
        if ($gridGroup)
        {
            $groupType = Request::get('group-type');
            $page = \View\View::getDom();

            if (is_array($groupType))
            {
                foreach ($groupType as $name => $value)
                {
                    $filterContent[] = $page->createSearchGroupField($grid, $name, $value);
                }
            }

            $selectGroup->addOption('*', 'Todos');
        }

        if (count($filterContent) > 0)
        {
            $btnSearch = new \View\Ext\Button('buscar', 'search', 'Buscar', "$('#buscar').click();", '', 'Clique para pesquisa');
            $btnSearch->setTitle('Buscar');
            $filterContent[] = $btnSearch;
        }

        if ($gridGroup && is_array($groupType))
        {
            $check[] = new \View\Label(NULL, 'makeGraph', 'Gráfico');
            $check[] = $select = new \View\Select('makeGraph', \View\Ext\HighChart::listChartTypes(), Request::get('makeGraph'));
            $select->change("$('#buscar').click();");
            $filterContent[] = new \View\Span(NULL, $check, 'makeGraphHold');
        }

        $result[] = new \View\Div('containerFiltros', $filterContent, 'clearfix');

        return $result;
    }

    public function listAvoidPropertySerialize()
    {
        $avoid = parent::listAvoidPropertySerialize();
        $avoid[] = 'grid';
        $avoid[] = 'created';
        $avoid[] = 'extraFilters';

        return $avoid;
    }

}