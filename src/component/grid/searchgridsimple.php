<?php

namespace Component\Grid;

class SearchGridSimple extends \Component\Grid\SearchGrid
{

    public function onCreate()
    {
        //avoid double creation
        if ($this->isCreated())
        {
            return $this->getContent();
        }

        $content = [];
        $content[] = $this->content = $this->createTable();

        $filterSmart = new \Filter\Smart();

        $fields = [];
        $fields[] = new \View\Label(null, 'q', 'Pesquisa esperta', 'filterLabel');
        $fields[] = $filterSmart->getInput();

        $smartSearch = new \View\Div(null, $fields, 'filterField');

        $this->byId('filters-holder')->append($smartSearch);

        \App::addJs("mountExtraFiltersLabel();");
        $buscar = $this->byId('buscar');
        $this->byId('tab-filter')->append($buscar);

        $this->byId('advancedFiltersList')->removeAttr('multiple');

        return $this->content;
    }

    public function createTable()
    {
        $content = [];

        if ($this->getCreateTab('filter'))
        {
            $fields = $this->mountAdvancedFiltersMenu();
            $content[] = new \View\Div('tab-filter', $fields, 'filter');
        }

        $content[] = $this->createTableInner();
        $div = new \View\Div($this->getId(), $content, 'grid search-grid-simple');
        //put link on js side
        $div->data('link', $this->getLink(null, null, null, false));

        return $div;
    }

    protected function createTabFilter2()
    {
        $tabItem = null;



        return $tabItem;
    }

}
