<?php

namespace Component\Grid;

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
        $count = $this->getDataSource()->getCount();
        $this->dataSource->setPaginationLimit(15);

        if (!$this->dataSource->getPage())
        {
            $this->dataSource->setPage(0);
        }

        $label = $this->getModelLabel();
        $view = array();

        if ($count == 0)
        {
            $th[] = new \View\Td(null, 'Nenhum ' . lcfirst($label . ' encontrado.'));
            $tr = new \View\Tr(null, $th);
            $view[] = $this->head = new \View\THead(NULL, $tr);
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
        $captions[] = '(' . $count . ') ' . $this->getModelLabelPlural();

        //$accordionId = strtolower(str_replace('\\', '_', $this->getId()));
        $accordion = new \View\Ext\Accordion($this->getId() . '-holder', $captions, $this->table, 'col-12 ');

        $urlAdd = "return p('{$this->getPageName()}/{$this->getAddMethod()}')";

        $buttons = new \View\Ext\Button('btnAdd' . $semAcento, 'plus', 'Adicionar ' . lcfirst($label), $urlAdd, 'success small');

        $btnHolder = new \View\Div('btnSearchButtons', $buttons, 'gridButtonsSearch');

        $accordion->getHead()->append($btnHolder);

        return $accordion;
    }

    public function getModelLabelPlural()
    {
        $model = $this->getModel();
        return $model::getLabelPlural();
    }

    public function getModelLabel()
    {
        $model = $this->getModel();
        return $model::getLabel();
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
