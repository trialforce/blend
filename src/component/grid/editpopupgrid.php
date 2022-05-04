<?php

namespace Component\Grid;

use DataHandle\Request;

/**
 * Grid for edit data in popoup
 */
class EditPopupGrid extends \Component\Grid\Grid
{

    /**
     * Edit method
     *
     * @var string
     */
    protected $editMethod = NULL;

    /**
     * Add method
     * @var string
     */
    protected $addMethod = null;

    /**
     * Remove method
     * @var string
     */
    protected $removeMethod = NULL;

    /**
     * Id parent
     * @var type
     */
    protected $idParent = NULL;

    /**
     * Model
     * @var \Db\Model
     */
    protected $model = NULL;

    /**
     * Construct the grid
     *
     * @param string $id
     * @param string $dataSource
     * @param string $class
     */
    public function __construct($id = NULL, $dataSource = NULL)
    {
        parent::__construct($id, $dataSource);
    }

    /* public function getPaginator()
      {
      return new \Component\Grid\EditPopupPaginator('paginator-' . $this->getGridName(), $this);
      } */

    public function setActions($actions)
    {
        $actions = is_array($actions) ? $actions : array();
        $columns = $this->getColumns();

        //create default editr action, if needed
        if (isset($columns['id']) && !isset($actions['id']) && $this->getEditMethod())
        {
            $editar = new \Component\Action\Page($this->getPageName(), $this->getEditMethod(), $this->getModel()->getId(), 'edit', 'Editar');
            $editar->setRenderInEdit(FALSE)->setRenderInGrid(TRUE)->setRenderInGridDetail(TRUE);

            $actionsBefore[] = $editar;
        }

        //fallback
        $url = $this->getRemoveMethod() ? $this->getRemoveMethod() : $this->getPageName() . '/removerItem/&_id=:id?';
        $actionsBefore[] = $remove = new \Component\Action\Action('removeitem', 'trash', 'remover', $url);
        $remove->setRenderInGrid(true);

        $actions = array_merge($actionsBefore, $actions);

        return parent::setActions($actions);
    }

    public function getLink($event = NULL, $value = NULL, $params = NULL, $putUrl = TRUE)
    {
        return \Component\Component::getLink($event, $value, $params);
    }

    /**
     * Retorna o modelo do datasource
     * @return \Db\Model
     */
    public function getModel()
    {
        if (is_null($this->model) && $this->getDataSource() instanceof \DataSource\Model)
        {
            $this->model = $this->getDataSource()->getModel();
        }

        return $this->model;
    }

    public function setModel($model)
    {
        $this->model = $model;
        return $this;
    }

    /**
     * Retorna o código do pai relacionado
     *
     * @return int o código do pai relacionado
     */
    public function getIdValue()
    {
        $model = $this->getModel();
        $model->setData(Request::getInstance());
        $id = Request::get('v') ? Request::get('v') : Request::get('id');
        return $id;
    }

    public function createCsv()
    {
        $this->addFilterToDataSource();
        $result = parent::createCsv();

        return $result;
    }

    protected function addFilterToDataSource()
    {
        //don't add filter again
        if (isset($this->filterAdd))
        {
            return;
        }

        $dataSource = $this->getDataSource();
        $idValue = $this->getIdValue();

        if ($idValue)
        {
            $dataSource->where($this->getIdParent(), '=', $idValue);
        }

        \Component\Grid\Grid::addPaginationToDataSource($dataSource);

        $this->filterAdd = true;
    }

    public function createTableInner()
    {
        if (!$this->actions)
        {
            $this->setActions(null);
        }

        $this->addFilterToDataSource();
        $model = $this->getModel();
        $label = $model::getLabel();

        $semAcento = \Type\Text::get($label)->toFile();
        $title[] = $label;

        $urlAdd = "p('{$this->getPageName()}/{$this->getAddMethod()}')";
        $buttons[] = new \View\Ext\Button('btnAdd' . $semAcento, 'plus', 'Adicionar', $urlAdd, 'success small');

        $title[] = new \View\Div('btnSearchButtons', $buttons, 'gridButtonsSearch');

        $this->setTitle($title);

        $fields = parent::createTableInner();

        return $fields;
    }

    public function onCreate()
    {
        $div = parent::onCreate();
        $div->addClass('edit-popup-grid clearfix');

        return $div;
    }

    public function getEditMethod()
    {
        return $this->editMethod;
    }

    public function setEditMethod($editMethod)
    {
        $this->editMethod = $editMethod;

        if (!$this->addMethod)
        {
            $this->addMethod = $editMethod;
        }

        return $this;
    }

    public function getAddMethod()
    {
        return $this->addMethod;
    }

    public function setAddMethod($addMethod)
    {
        $this->addMethod = $addMethod;
        return $this;
    }

    public function setAutoEditMethod()
    {
        if ($this->getDataSource() instanceof \DataSource\Model)
        {
            $this->setEditMethod('editarDialog&modelName=' . str_replace('\\', '\\\\', $this->getDataSource()->getModel()) . '&gridName=' . $this->getIdJs() . '&idParent=' . $this->getIdParent());
        }
    }

    public function getRemoveMethod()
    {
        return $this->removeMethod;
    }

    public function setRemoveMethod($removeMethod)
    {
        $this->removeMethod = $removeMethod;
        return $this;
    }

    public function getIdParent()
    {
        return $this->idParent;
    }

    public function setIdParent($idParent)
    {
        $this->idParent = $idParent;
        return $this;
    }

    public function getColumns()
    {
        $columns = parent::getColumns();

        foreach ($columns as $column)
        {
            if ($column instanceof \Component\Grid\EditColumn)
            {
                $column->setEditEvent($this->getEditMethod());
                $column->setOrder(FALSE);
            }
        }

        $newColumns = array();

        if (!isset($columns['id']))
        {
            $newColumns['id'] = $action = new \Component\Grid\PkColumnEdit('id', 'Ações');
            //don't allow edition with double click, by default
            $action->setGrid($this)->setEditEvent(null);
        }

        return array_merge($newColumns, $columns);
    }

    public function setColumns($columns)
    {
        foreach ($columns as $column)
        {
            if ($column instanceof \Component\Grid\EditColumn)
            {
                $column->setEditEvent($this->getEditMethod());
            }
        }

        return parent::setColumns($columns);
    }

    protected function createTr($columns, $index, $item)
    {
        $beforeGridCreateRow = false;
        $afterGridCreateRow = false;
        $dom = \View\View::getDom();

        $beforeGridCreateRow = $dom instanceof \Page\BeforeGridCreateRow;
        $afterGridCreateRow = $dom instanceof \Page\AfterGridCreateRow;

        if ($beforeGridCreateRow)
        {
            $dom->beforeGridCreateRow($item, $index, NULL);
        }

        $tr = parent::createTr($columns, $index, $item);

        if ($afterGridCreateRow)
        {
            \View\View::getDom()->afterGridCreateRow($item, $index, $tr);
        }

        return $tr;
    }

    public function afterGridCreateRow($item, $line, \View\Tr $tr)
    {
        $name = $item::getName();
        $columnAtivo = $name::getColumn('ativo');

        if ($columnAtivo instanceof \Db\Column\Column && $item->ativo . '' < 1)
        {
            $tr->addClass('desativado');
        }

        $columnSituacao = $name::getColumn('situacao');

        $situacaoValue = $item->getValue('situacao');

        if ($situacaoValue instanceof \Type\Generic)
        {
            $situacaoValue = $situacaoValue->toDb();
        }

        if ($columnSituacao instanceof \Db\Column\Column && $situacaoValue < 1)
        {
            $tr->addClass('desativado');
        }
    }

}
