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

        //$this->createTabColumn($tab);
        //$this->createTabGroup($tab);
        //$this->createTabSave($tab);
        //$this->setContent($tab);
        //$filterSmart = new \Filter\Smart();
        //$this->byId('tab-holder-search-fieldHead')->append($filterSmart->getInput());
        //$content[] = $filters;
        $content[] = $this->content = $this->createTable();
        ;

        \App::addJs("mountExtraFiltersLabel();");

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
