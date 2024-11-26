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
        $iframe = \DataHandle\Request::get('iframe');
        $pageUrl = \View\View::getDom()->getPageUrl();
        $pageUrl = $iframe ? $pageUrl.'?iframe=true' : $pageUrl;
        $idQuestion = $this->getFilterName() ? $this->getFilterName() : 'q';
        $idBtn = 'buscar';
        $search = new \View\Input($idQuestion, \View\Input::TYPE_SEARCH, Request::get($idQuestion), '');

        $search->setAttribute('placeholder', 'Pesquisa rápida...')
                ->setClass('search fullWidth')
                ->setValue(Request::get($idQuestion))
                ->setTitle('Digite o conteúdo a buscar...')
                ->onPressEnter('$("#' . $idBtn . '").click();');

        $fields = array();
        $fields[] = $reset = new \View\Ext\LinkButton('search-reset', 'undo', null, $pageUrl, 'cleann icon-only');
        $reset->setAjax(false)->setTitle('Clicar para limpar/resetar a busca');
        $fields[] = $search;
        $fields[] = $btn = new \View\Ext\Button('search-fast', 'search', null, '$("#' . $idBtn . '").click();', 'primary icon-only');
        $btn->setTitle('Clicar para executar a busca');

        return new \View\Div('main-search', $fields, 'filterField');
    }

    public function createWhere($index = 0)
    {
        //TODO use this and don't use datasource smartFilter property
        return null;
    }

}
