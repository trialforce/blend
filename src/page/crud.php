<?php

namespace Page;

use DataHandle\Get;
use DataHandle\Request;

/**
 * Automated CRUD page
 */
class Crud extends \Page\Page
{

    const EVENT_SEARCH = 'listar';
    const EVENT_INSERT = 'adicionar';
    const EVENT_UPDATE = 'editar';
    const EVENT_VIEW = 'ver';
    const EVENT_REMOVE = 'remover';
    const EVENT_SAVE = 'salvar';

    /**
     * Floating menu
     *
     * @var \View\Blend\FloatingMenu
     */
    protected $floatingMenu;

    /**
     * Modelo
     * @var \Db\Model
     */
    protected $model;

    /**
     * DataSource
     *
     * @var \DataSource\DataSource
     */
    protected $dataSource;

    public function __construct($model = NULL)
    {
        if (is_null($model))
        {
            $class = str_replace('Page\\', '\Model\\', get_class($this));
            $model = new $class();
        }

        if ($this->getPopupAdd())
        {
            $this->setPopupAdd(true);
        }

        $this->setModel($model);
        parent::__construct();
    }

    public function getPopupAdd()
    {
        return $this->popupAdd || $this->getFormValue('popupAdd') || Request::get('popupAdd') || Get::get('popupAddRedirectPage');
    }

    /**
     * Return the form title element
     *
     * @return \View\Span
     */
    public function getFormTitle()
    {
        $formName = '';

        if (method_exists($this, 'getFormName'))
        {
            $formName = $this->getFormName();
        }

        $btnSearch = null;

        if ($this->getEvent() == 'listar')
        {
            $btnSearch = new \View\Ext\Icon('search');
            $btnSearch->addClass('hide-in-desktop search-icon');
            $btnSearch->click('$("#searchHead").toggleClass("hide-in-mobile");');
        }

        return new \View\Span($formName . 'extraTitle', array($this->getIcon(), $this->getTitle(), $btnSearch));
    }

    /**
     * Get the model
     * //TODO this function has to be renamed, because it's not a get
     * model, it mount the model based on url
     *
     * @return \Db\Model
     */
    public function getModel($request = NULL, $model = NULL)
    {
        //caso padrão
        if (is_null($model))
        {
            $model = $this->model;
        }

        $class = get_called_class();
        $formName = $class::getFormName();

        if ($request)
        {
            if ($formName)
            {
                $request = new Request();
                $request->setData(Request::get($formName));

                $model->setData($request);
            }
            else
            {
                $model->setData($request);
            }
        }

        $this->model = $model;

        return $this->model;
    }

    /**
     * Retorna o nome/classe do modelo
     *
     * @return string
     */
    public function getModelName()
    {
        return '\\' . get_class($this->model);
    }

    /**
     * Define o modelo
     *
     * @param \Db\Model $model
     * @return \View\PageCrud
     */
    public function setModel(\Db\Model $model)
    {
        $this->model = $model;
        return $this;
    }

    /**
     * Get pk value from request
     *
     * @return int
     */
    public function getPkValue()
    {
        $pkValue = str_replace('/', '', Request::get('v'));

        //if not catch in 'v', try get from posted pk
        if (!($pkValue || $pkValue === 0 || $pkValue === '0'))
        {
            $model = $this->model;
            $pkName = $model->getPrimaryKey() . '';
            $pkValue = $this->getFormValue($pkName);
        }

        return $pkValue;
    }

    /**
     * Define the model based id from url or posted
     * @return \Db\Model Description
     */
    public function setModelFromIdUrl($throw = TRUE)
    {
        $pkValue = $this->getPkValue();

        $this->model = $this->model->findOneByPk($pkValue);

        if (!$this->model && $throw)
        {
            throw new \UserException('Impossível encontrar registro!');
        }

        return $this->model;
    }

    /**
     * Retorna o evento atual. Garante a busca caso o evento não exista
     */
    public function getEvent()
    {
        $event = Request::get('e');

        if (!$event)
        {
            $event = self::EVENT_SEARCH;
        }

        return $event;
    }

    /**
     * Retorn the datasource
     *
     * @return \DataSource\Model
     */
    public function getDataSource()
    {
        //avoid new data source
        if (!$this->dataSource)
        {
            $this->dataSource = new \DataSource\Model($this->getModel());
        }

        return $this->dataSource;
    }

    /**
     * Montagem da tela de adicionar
     */
    public function adicionar()
    {
        $this->setFocusOnFirstField();

        if ($this->popupAdd)
        {
            \App::dontChangeUrl();
            return $this->getPopup();
        }
        else
        {
            $this->append($this->getHead());
            $this->append($this->getBodyDiv($this->mountFieldLayout()));
        }

        $this->adjustFields();
    }

    public function getPopup()
    {
        $body[] = new \View\Div('popupHolder', $this->mountFieldLayout());

        $this->adjustFields();

        //add popupadd to form, to make ir post corret
        $body[] = new \View\Input($this->getInputName('popupAdd'), \View\Input::TYPE_HIDDEN, 'popupAdd');
        $body[] = new \View\Input('popupAdd', \View\Input::TYPE_HIDDEN, 'popupAdd');
        $body[] = new \View\Input('popupAddInputName', \View\Input::TYPE_HIDDEN, Request::get('popupAddInputName'));
        $body[] = new \View\Input('popupAddPageName', \View\Input::TYPE_HIDDEN, $this->getPageUrl());

        $buttons[] = $this->getTopButtons();

        $popup = new \View\Blend\Popup('popupAdicionar', $this->getTitle(), $body, $buttons, 'form ' . $this->getPageUrl());
        $popup->body->setId('bodyPopup');
        $popup->setIcon($this->icon);
        $popup->show();

        $this->byId('btnVoltar')->click(\View\Blend\Popup::getJs('destroy'));
    }

    /**
     * Monta um ou mais FieldLayout
     *
     * @return array de campos
     */
    protected function mountFieldLayout()
    {
        $fielLayouts = $this->getFieldLayout();

        if (!is_array($fielLayouts))
        {
            $fielLayouts = array($fielLayouts);
        }

        foreach ($fielLayouts as $layout)
        {
            $fields[] = $layout->onCreate();
        }

        return $fields;
    }

    public function editar()
    {
        $this->setFocusOnFirstField();
        $this->setModelFromIdUrl();

        if ($this->popupAdd)
        {
            \App::dontChangeUrl();
            $popup = $this->getPopup();
            $this->createFloatingMenu();
            $this->floatingMenu->addClass('action-list-popup');

            return $popup;
        }
        else
        {
            $this->append($this->getHead());
            $this->append($this->getBodyDiv($this->mountFieldLayout()));
            $this->createFloatingMenu();
        }

        $this->adjustFields();
    }

    public function createFloatingMenu()
    {
        $this->floatingMenu = new \View\Blend\FloatingMenu($this->getFloatingMenuId());
        $this->floatingMenu->addActions($this->getEditActions(), $this->getModel()->getId());
        $this->floatingMenu->addClass('action-list');
    }

    public function getFloatingMenuId()
    {
        return 'fm-action-' . str_replace('/', '-', $this->getPageUrl());
    }

    public function setDefaultActions()
    {
        $actions = array();

        if ($this->verifyPermission('editar'))
        {
            $editar = new \Component\Action\Page($this->getPageUrl(), 'editar', $this->getModel()->getId(), 'edit', 'Editar');
            $editar->setRenderInEdit(FALSE)->setRenderInGrid(TRUE)->setRenderInGridDetail(TRUE);
        }
        else
        {
            $editar = new \Component\Action\Page($this->getPageUrl(), 'ver', $this->getModel()->getId(), 'edit', 'Ver');
            $editar->setRenderInEdit(FALSE)->setRenderInGrid(TRUE)->setRenderInGridDetail(TRUE);
        }

        $actions[] = $editar;

        $js = "return grid.openTrDetail($(this).parents('tr'))";
        $openTrDetail = new \Component\Action\Action('openTrDetail', 'eye', 'Ver detalhes', $js, '');
        $openTrDetail->setRenderInEdit(FALSE)->setRenderInGrid(TRUE);

        $actions[] = $openTrDetail;

        if ($this->verifyPermission('remover'))
        {
            $actions[] = $remover = new \Component\Action\Remove($this->getModel()->getName(), $this->getModel()->getId());
            $remover->setRenderInGridDetail(TRUE);
        }

        return $actions;
    }

    public function getEditActions()
    {
        $actions = $this->setDefaultActions();
        $result = array();

        foreach ($actions as $action)
        {
            $action instanceof \Component\Action;

            if ($action->getRenderInEdit())
            {
                $result[] = $action;
            }
        }

        return $result;
    }

    public function ver()
    {
        $campos = $this->editar();
        \App::addJs("preparaVer();");
        return $campos;
    }

    /**
     * Retorna o FieldLayout utilizado para montagem dos campos
     *
     * @return \Fieldlayout\Vector
     */
    public function getFieldLayout()
    {
        return new \Fieldlayout\Vector(null, $this->model);
    }

    /**
     * Return page title
     *
     * @return string
     */
    public function getTitle()
    {
        $extraLabel = '';

        if ($this->isUpdate())
        {
            $extraLabel = \Type\Text::get($this->model->getTitleLabel())->ellipsis(60) . '';
            $extraLabel = $extraLabel ? $extraLabel = ' - ' . $extraLabel : '';
        }

        return ucfirst($this->getEvent()) . ' ' . $this->getLcModelLabel() . $extraLabel;
    }

    /**
     * Verifica se está em modo de inserção
     *
     * @return boolean
     */
    public function isInsert()
    {
        return $this->getEvent() == self::EVENT_INSERT;
    }

    /**
     * Verificar se está em modo de edição
     *
     * @return boolean
     */
    public function isUpdate()
    {
        return $this->getEvent() == self::EVENT_UPDATE || $this->getEvent() == self::EVENT_VIEW;
    }

    /**
     * Verifica se está em modo de busca
     *
     * @return boolean
     */
    public function isSearch()
    {
        return !($this->isInsert() || $this->isUpdate());
    }

    /**
     * Verifica se é update or insert
     *
     * @return boolean
     */
    public function isUpdateOrInsert()
    {
        return !$this->isSearch();
    }

    /**
     * Return top buttons
     *
     * @return array
     */
    public function getTopButtons()
    {
        $buttons = NULL;

        if (!$this->isSearch())
        {
            $buttons[] = $btnSalvar = new \View\Ext\Button('btnSalvar', 'save', 'Gravar ' . $this->getLcModelLabel(), 'salvar', 'save btninserir primary');
            $btnSalvar->setTitle('Salva o registro atual no banco de dados!')->setDisabled();

            $buttons[] = $btnVoltar = new \View\Ext\Button('btnVoltar', 'arrow-left', 'Voltar', 'history.back(1);');
            $btnVoltar->setTitle('Volta para a listagem!')->formChangedAdvice();

            if ($this->isUpdate())
            {
                $idFMenu = str_replace('/', '-', $this->getPageUrl());
                $btnAction = new \View\Div('floating-menu-' . $idFMenu, array(new \View\Ext\Icon('wrench'), new \View\Span(null, 'Ações', 'btn-label'), $this->floatingMenu), 'btn clean blend-floating-menu-holder action-list-toogle');
                $btnAction->click('return actionList.toggle();');
                $buttons[] = $btnAction;
            }
        }
        else
        {
            $buttons = $this->getTopButtonsSearch();
        }

        return $buttons;
    }

    /**
     * Return the model label lower case first
     *
     * @return string
     */
    public function getLcModelLabel()
    {
        if ($this->model)
        {
            if ($this->isSearch())
            {
                return lcfirst($this->model->getLabelPlural());
            }
            else
            {
                return lcfirst($this->model->getLabel());
            }
        }

        return '';
    }

    public function getTopButtonsSearch()
    {
        $adicionar = 'Adicionar ' . lcfirst($this->model->getLabel());
        $buttons[] = $btnInsert = new \View\Ext\LinkButton('btnInsert', 'plus', $adicionar, $this->getPageUrl() . '/' . self::EVENT_INSERT, 'btn btninserir success');
        $btnInsert->setTitle('Abre a tela para digitação de um novo cadastro!');

        if (!$this->verifyPermission('adicionar'))
        {
            $btnInsert->disable();
        }

        return $buttons;
    }

    /**
     * Retorna somente os itens selecionados da listagem
     *
     * @param array $checks
     */
    public function onlySelecteds($checks)
    {
        $ids = array();

        if (is_array($checks))
        {
            foreach ($checks as $id)
            {
                if ($id)
                {
                    $ids[] = $id;
                }
            }
        }

        return $ids;
    }

    /**
     * @deprecated since version 201-10-2
     */
    public function remover()
    {
        \App::dontChangeUrl();

        $footer[1] = new \View\Button('cancelar', array(new \View\Ext\Icon('arrow-left'), 'Não'), \View\Blend\Popup::getJs('destroy'), 'btn');

        $footer[0] = new \View\Button('confirmaRemocao', array(new \View\Ext\Icon('trash-o'), 'Sim'), 'confirmaExclusao', 'btn danger');
        $footer[0]->setAutoFocus();
        $footer[0]->focus();

        $body[] = 'Confirma remoção do registro?';

        //add support for popup remove inside gridpopup
        if ($this->getPopupAdd())
        {
            $body[] = new \View\Input('popupAdd', 'hidden', 'popupAdd');
            $body[] = new \View\Input('_id', 'hidden', Request::get('_id'));
        }

        $popup = new \View\Blend\Popup('remocao', 'Confirmar remoção...', $body, $footer);
        $popup->show();
    }

    /**
     * @deprecated since version 201-10-2
     */
    public function confirmaExclusao()
    {
        \App::dontChangeUrl();
        \View\Blend\Popup::delete();

        $model = $this->getModel();
        $pk = $model->getPrimaryKey();

        if (!$pk)
        {
            throw new \UserException('Imposível encontrar chave primária do modelo!');
        }

        $pkValue = $this->getFormValue($pk);

        if ($this->getPopupAdd())
        {
            $pkValue = Request::get('_id');
        }

        $model->setValue($pk, $pkValue);

        try
        {
            $ok = $model->delete();

            if ($ok)
            {
                toast('Registro removido com sucesso!!', 'success');

                if ($this->getPopupAdd())
                {
                    \View\Blend\Popup::delete();
                }
                else
                {
                    \App::addjs('history.back(1);');
                }
            }
            else
            {
                toast('Problemas ao remover o registro!', 'danger');
            }

            return $ok;
        }
        catch (\UserException $exc)
        {
            toast($exc->getMessage(), 'danger');
            return false;
        }
        catch (\Exception $exc)
        {
            if ($exc instanceof \PDOException)
            {
                toast('Problemas ao remover o registro! <br>Verifique se não existe algum registro que depende deste cadastro!', 'danger');
            }
            else
            {
                throw $exc;
            }
        }
    }

    /**
     * Faz o salvamento do registro
     *
     * @param \Db\Model $model
     * @param boolean $defaultRedirect
     * @return int
     */
    public function salvar($model = NULL, $defaultRedirect = TRUE)
    {
        if (is_null($model))
        {
            $model = $this->getModel(Request::getInstance());
        }

        return parent::salvar($model, $defaultRedirect);
    }

    public function defaultRedirect($mensagem = 'OK! Gravado!', $type = 'success')
    {
        if ($this->getPopupAdd())
        {
            //TODO update grid
            \View\Blend\Popup::delete();
        }
        else
        {
            \App::dontChangeUrl();
            toast($mensagem, $type);
            \App::redirect($this->getPageUrl(), TRUE);
        }
    }

    /**
     * Duplicate the currente mdoel, ask confirmation
     */
    public function duplicar()
    {
        \App::dontChangeUrl();

        $view[] = new \View\Label('label', null, 'Tem certeza que deseja duplicar o registro?');

        \View\Blend\Popup::prompt('Duplicação', $view, 'duplicarConfirmado', null, 'small')->show();
    }

    /**
     * Really duplicate current model
     */
    public function duplicarConfirmado()
    {
        \App::dontChangeUrl();
        $model = $this->setModelFromIdUrl();
        $model->duplicate();

        \App::redirect($this->getPageUrl() . '/editar/' . $model->getId(), true);

        toast('Registro duplicado com sucesso !');
    }

    public function editarDialog()
    {
        throw new \Exception('Not working anymore!');
    }

    public function parseEvent($event)
    {
        //caso o evento seja salvar
        if ($event == 'salvar')
        {
            $pkName = $this->model->getPrimaryKey()->getName();

            if ($this->getFormValue($pkName))
            {
                $event = self::EVENT_UPDATE;
            }
            else
            {
                $event = self::EVENT_INSERT;
            }
        }

        return parent::parseEvent($event);
    }

    /**
     * Add a button in btnGroup
     *
     * @param \View\View $button
     */
    public function addButton($button)
    {
        $this->byId('btnGroup')->append($button);

        return $this;
    }

    /**
     * Edit a value of a model from a Grid
     * //TODO this method must be passed to \Component\Grid\Grid
     */
    public function gridEdit()
    {
        \App::dontChangeUrl();
        $columnName = Request::get('columnName');
        $pkValue = str_replace('/', '', Request::get('v'));
        $this->setModelFromIdUrl();
        $model = $this->getModel();
        $column = $model->getColumn($columnName);
        $fieldLayout = $this->getFieldLayout();

        if (is_array($fieldLayout))
        {
            $fieldLayout = $fieldLayout[0];
        }

        $input = $fieldLayout->getInputField($column);
        $input->setValue($model->getValue($columnName));
        $pageUrl = $this->getPageUrl();
        $input->blur("p('{$pageUrl}/saveGridEdit/$pkValue/?columnName={$columnName}');");

        $elementId = 'gridColumn-' . $columnName . '-' . trim($pkValue);
        $this->byId($elementId)->html($input)->attr('onclick', '');
    }

    /**
     * Save the edited value from Grid
     * //TODO this method must be passed to \Component\Grid\Grid
     */
    public function saveGridEdit()
    {
        \App::dontChangeUrl();
        //get column name from request
        $columnName = Request::get('columnName');
        //set model to page from url
        $this->setModelFromIdUrl();
        //store model variable to use later
        $model = $this->getModel();
        //get column from model
        $column = $model->getColumn($columnName);
        //get the column property (alias for columna name)
        $columnProperty = $column->getProperty();
        //get the posted edited value
        $value = Request::get($columnProperty);
        //define the modified value in current model
        $model->setValue($columnName, $value);

        //get pk column to force only update what is neeed
        $pkColumn = $model->getPrimaryKey();
        //lista only columns needed in update
        $columns[$pkColumn . ""] = $pkColumn;
        $columns[$columnName] = $column;
        //update the model in database
        $model->update($columns);
        //refresh the page
        \App::refresh(TRUE);
    }

    /**
     * Return true if the search is filtred
     *
     * @return boolean
     */
    public function isFiltred()
    {
        $q = Request::get('q');
        $isFiltred = strlen($q) > 0;
        $request = Request::getInstance();

        foreach ($request as $var => $value)
        {
            if (stripos($var, 'value') > 0)
            {
                if (is_array($value))
                {
                    foreach ($value as $idx => $valuex)
                    {
                        if (strlen($valuex) > 0)
                        {
                            $isFiltred = true;
                        }
                    }
                }
                else
                {
                    if (strlen($value) > 0)
                    {
                        $isFiltred = true;
                    }
                }
            }
        }

        return $isFiltred;
    }

    /**
     * List all acl events
     *
     * @return array
     */
    public static function listAclEvents()
    {
        $events['listar'] = 'Listar';
        $events['adicionar'] = 'Adicionar';
        $events['editar'] = 'Editar';
        $events['remover'] = 'Remover';

        return $events;
    }

    /**
     * Create a fixed filter
     *
     * @deprecated since version 2019-04-13
     *
     * @param string $idColumn
     * @param array $options
     * @param mixed $defaultValue
     * @param string $allLabel
     * @return \View\Select
     */
    public function createFixedFilter($idColumn, $options, $defaultValue = '', $allLabel = 'Todos', $onlyFilter = false)
    {
        $grid = $this->getGrid();
        $ds = $grid->getDataSource();
        $realColumnName = \Db\Column\Column::getRealColumnName($idColumn);
        $column = $ds->getColumn($realColumnName);

        if (!$column)
        {
            $label = ucfirst(str_replace('id', '', $realColumnName));
            $column = new \Component\Grid\Column($realColumnName, $label, \Component\Grid\Column::ALIGN_LEFT, \Db\Column\Column::TYPE_VARCHAR);
        }

        $column->setSql($idColumn);


        $collection = new \Db\Collection($options);
        $filter = new \Filter\Collection($column, $collection);
        $filter->setDefaultValue($defaultValue);

        if (!$onlyFilter)
        {
            $grid->getSearchField()->addExtraFilter($filter);
        }

        $ds->addExtraFilter($filter->getDbCond());

        return $filter;
    }

    /**
     * Create a fixed filter interval type
     *
     * @deprecated since version 2019-04-13
     *
     * @param string $idColumn
     * @param string $options
     * @param string $defaultValue
     * @param string $allLabel
     * @return \View\Select
     */
    public function createFixedIntervalFilter($idColumn, $defaultValueBegin = '', $defaultValueEnd = '', $onlyFilter = false)
    {
        $grid = $this->getGrid();
        $ds = $grid->getDataSource();
        $realColumnName = \Db\Column\Column::getRealColumnName($idColumn);
        $column = $ds->getColumn($realColumnName);

        if (!$column)
        {
            $label = ucfirst(str_replace('id', '', $realColumnName));
            $column = new \Component\Grid\Column($realColumnName, $label, \Component\Grid\Column::ALIGN_LEFT, \Db\Column\Column::TYPE_VARCHAR);
        }

        $column->setSql($idColumn);

        $filter = new \Filter\DateInterval($column, $idColumn);

        if ($defaultValueBegin)
        {
            $filter->setDefaultValue($defaultValueBegin);
        }

        if ($defaultValueEnd)
        {
            $filter->setDefaultValueFinal($defaultValueEnd);
        }

        if (!$onlyFilter)
        {
            $grid->getSearchField()->addExtraFilter($filter);
        }

        $ds->addExtraFilter($filter->getDbCond());

        return $filter;
    }

    /**
     * Get fixed filter value
     *
     * @param string $variable
     * @param string $defaultValue
     * @return string
     */
    public function getFixedFilterValue($variable, $defaultValue = '1')
    {
        return isset($_REQUEST[$variable]) ? Request::get($variable) : $defaultValue;
    }

    public static function getFormName()
    {
        return '';
    }

    /**
     * Return a "name" for html input considering the form name
     *
     * @param string $id
     * @return string
     */
    public function getInputName($id)
    {
        $class = get_called_class();
        $formName = $class::getFormName();

        if ($formName)
        {
            return $formName . '[' . $id . ']';
        }
        else
        {
            return $id;
        }
    }

    /**
     * Get form value
     *
     * @param string $var
     * @return string
     */
    public function getFormValue($var)
    {
        $class = get_called_class();
        $formName = $class::getFormName();

        if ($formName && $var)
        {
            //convert object to string if needed
            $var = $var . '';
            $formValues = Request::get($formName);

            if (isset($formValues[$var]))
            {
                return $formValues[$var];
            }
        }
        else
        {
            return Request::get($var);
        }

        return null;
    }

    /**
     * Return main div "#divLegal"
     *
     * @return \View\View
     */
    public function getMainDiv()
    {
        if ($this->getPopupAdd())
        {
            return $this->byId('popupHolder');
        }
        else
        {
            return $this->byId('divLegal');
        }
    }

    public function openTrDetail()
    {
        return $this->getGrid()->openTrDetail();
    }

    /**
     * Return the html from the js call printScreen();
     *
     * @return string
     */
    protected function getPrintScreenHtml()
    {
        $cssPath = BLEND_PATH . '/pdfprintscreen.css';
        $cssFile = new \Disk\File($cssPath, true);
        $css = $cssFile->getContent();

        $html = '<html>';
        $html .= '<style>' . $css . '</style>';
        $html .= '<body>';
        $html .= '<h1>' . Request::get('title') . '</h1>';
        $html .= Request::get('content');
        $html .= '</body>';
        $html .= '</html>';

        return $html;
    }

    /**
     * Called from js printScreen()
     */
    public function printScreen()
    {
        \App::dontChangeUrl();
        $type = Request::get('type') ? Request::get('type') : 'pdf';

        $filePath = str_replace('-', '_' . $this->getPageUrl()) . '_';
        $filePath .= \Type\DateTime::now()->format(\Type\DateTime::MASK_TIMESTAMP_FILE);
        $filePath .= '.' . $type;

        $file = \Disk\File::getFromStorage($filePath);
        $file->createStorageFolderIfNeeded();

        $html = $this->getPrintScreenHtml();

        if ($type == 'pdf')
        {
            $pdf = new \ReportTool\WkPdf('utf-8', 'A4', '', '', 5, 5, 5, 5);
            $pdf->WriteHTML($html);
            $pdf->Output($file->getPath());

            $file->outputToBrowser(TRUE);
        }
        else
        {
            $file->save($html);
            $file->outputToBrowser(TRUE);
        }
    }

    public function columnQuestion()
    {
        \App::dontChangeUrl();
        $columnName = $this->getPkValue();
        $model = $this->getModel();
        $column = $model->getColumn($columnName);

        \View\Blend\Popup::alert($column->getLabel(), $column->getDescription())->show();
    }

    /**
     * Open a popup of this crud.
     * It uses a internal iframe soluction to avoind mixing the forms post and values.
     */
    public function editarPopup()
    {
        \App::dontChangeUrl();
        $idInput = Request::get('idInput');
        $id = Request::get('v');
        //edit or add
        $url = $id ? $this->getPageUrl() . '/editar/' . $id : $this->getPageUrl() . '/adicionar/';
        $url .= '?iframe=true';

        $title = ucfirst(($id ? 'editar' : 'adicionar') . ' ' . lcfirst($this->model->getLabel()));

        $body = new \View\IFrame('edit-popup-iframe', $url);
        $body->setWidth('100', '%')->setHeight('70', 'vh');
        $buttons = null;

        $popup = new \View\Blend\Popup('edit-popup', $title, $body, $buttons, 'popup-full-body form ' . $this->getPageUrl());
        $popup->setIcon($this->icon);
        $popup->footer->remove();
        $popup->show();

        //allow to update the original input if needed
        if ($idInput)
        {
            $this->byId('btbClosePopup')->click("return comboModelClose('{$idInput}')");
        }
    }

}
