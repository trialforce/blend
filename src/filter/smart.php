<?php

namespace Filter;

use DataHandle\Request;

/**
 * Smart Filter
 */
class Smart extends \Filter\Text
{

    public function getInput()
    {

        $idQuestion = $this->getFilterName() ? $this->getFilterName() : 'q';
        $idBtn = 'buscar';
        $search = new \View\Input($idQuestion, \View\Input::TYPE_SEARCH, Request::get($idQuestion));

        $search->setAttribute('placeholder', 'Pesquisa rápida...')
                ->setClass('search fullWidth')
                ->setValue(Request::get($idQuestion))
                ->setTitle('Digite o conteúdo a buscar...')
                ->onPressEnter('$("#' . $idBtn . '").click();');

        $fields = array();
        //$fields[] = new \View\Label(null, 'q', 'Pesquisar', 'filterLabel');
        $fields[] = $search;
        $fields[] = new \View\Ext\Button('search-fast', 'search', null, '$("#' . $idBtn . '").click();', 'icon-only');

        return new \View\Div('main-search', $fields, 'filterField');
    }

    public function createWhere($index = 0)
    {
        //TODO use this and don't use datasource smartFilter property
        return null;
    }

}
