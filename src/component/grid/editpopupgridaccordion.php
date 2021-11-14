<?php

namespace Component\Grid;

use DataHandle\Request;

/**
 * Grid for edit data in popoup
 */
class EditPopupGridAccordion extends \Component\Grid\EditPopupGrid
{

    public function createTableInner()
    {
        if (!$this->actions)
        {
            $this->setActions(null);
        }

        $this->addFilterToDataSource();
        $data = $this->getDataSource()->getData();
        $model = $this->getModel();
        $label = $model::getLabel();
        $view = array();

        //$caption = new \View\Div($captionName, $captions);
        //$caption->setAttribute('style', 'height: 40px;    line-height: 40px;    font-weight: bold;');

        if (count($data) == 0)
        {
            $th[] = new \View\Td(null, 'Nenhum ' . lcfirst($label . ' encontrado.'));
            $tr = new \View\Tr(null, $th);
            $view[] = $this->head = new \View\THead(NULL, $tr);
            //$view[] = $this->body = new \View\TBody(NULL, null);
            //$view[] = $this->foot = null;
        }
        else
        {
            $view[] = $this->mountColGroup();
            $view[] = $this->head = new \View\THead(NULL, $this->mountHead());
            $view[] = $this->body = new \View\TBody(NULL, $this->mountData());
            $view[] = $this->foot = $this->mountFoot();
        }

        $this->table = new \View\Table($this->getId() . 'Table', $view, 'table-grid');

        $this->makeAggregation();

        $semAcento = \Type\Text::get($label)->toFile();

        $captions = [];
        $captions[] = '(' . count($data) . ') ' . $model::getLabelPlural();

        //$accordionId = strtolower(str_replace('\\', '_', $this->getId()));
        $accordion = new \View\Ext\Accordion($this->getId() . '-holder', $captions, $this->table, ' grid grid-accordion col-12 ');

        $urlAdd = "return p('{$this->getPageName()}/{$this->getAddMethod()}')";

        $buttons = new \View\Ext\Button('btnAdd' . $semAcento, 'plus', 'Adicionar ' . lcfirst($label), $urlAdd, 'success small');

        $btnHolder = new \View\Div('btnSearchButtons', $buttons, 'gridButtonsSearch');

        $accordion->getHead()->append($btnHolder);

        return $accordion;
    }

    public function createTable()
    {
        $grid = parent::createTable();
        $grid->addClass('grid-accordion');

        return $grid;
    }

    public function listar()
    {
        $accordion = parent::listar();

        $accordion->open();

        return $accordion;
    }

    public function getPaginator()
    {
        return new \Component\Grid\EditPopupPaginatorAccordion('paginator', $this);
    }

}
