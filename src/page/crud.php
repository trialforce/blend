<?php

namespace Page;

use DataHandle\Request;
use DataHandle\Get;
use \View\Ext\HighChart;

/**
 * Automated CRUD page
 */
class Crud extends \Page\Page
{

    const EVENT_SEARCH = 'listar';
    const EVENT_INSERT = 'adicionar';
    const EVENT_UPDATE = 'editar';
    const EVENT_REMOVE = 'remover';
    const EVENT_SAVE = 'salvar';

    protected $popupAdd = FALSE;

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
            $class = '\Model\\' . str_replace('Page\\', '', get_class($this));
            $model = new $class();
        }

        if ($this->getPopupAdd())
        {
            $this->setPopupAdd(true);
        }

        $this->setModel($model);
        parent::__construct();
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
     * Create a DataSource for group select
     *
     * @return \DataSource\Model
     */
    public function getDataSourceAgg()
    {
        $contantValues = null;
        $groupType = Request::get('group-type');
        $columns = NULL;

        if (is_array($groupType))
        {
            $ds = new \DataSource\ModelGroup($this->getModel());

            foreach ($groupType as $name => $agg)
            {
                $column = $this->model->getColumn($name);
                $label = $name;
                $type = \Db\Column::TYPE_DECIMAL;

                if ($column instanceof \Db\Column)
                {
                    $type = $column->getType();

                    if (!$agg || $agg == \Db\GroupColumn::METHOD_GROUP)
                    {
                        $label = $column->getLabel();
                    }
                    else
                    {
                        $label = $column->getLabel() . '(' . \Db\GroupColumn::getMethodLabel($agg) . ')';
                    }

                    $query = $column->getName();

                    if ($column->getReferenceDescription())
                    {
                        $column->setTableName($this->model->getTableName());
                        $query = $column->getReferenceSql(FALSE);

                        $type = \Db\Column::TYPE_VARCHAR;
                    }
                    else if ($column instanceof \Db\Column)
                    {
                        $sql = $column->getSql(FALSE);
                        $query = $sql[0];
                    }

                    if ($column->getConstantValues())
                    {
                        $type = \Db\Column::TYPE_VARCHAR;
                    }
                }

                if ($label == '*')
                {
                    $query = '*';
                    $label = \Db\GroupColumn::getMethodLabel($agg) . ' (Tudo)';
                    $name = $agg;
                }

                $columns[$name] = new \Db\GroupColumn($label, $agg, $query, $name, $type);
            }

            $ds->setColumns($columns);

            return $ds;
        }

        return NULL;
    }

    /**
     * Create Group Grif
     */
    public function createGroupGrid()
    {
        if (Request::get('makeGraph'))
        {
            return $this->makeGraph();
        }

        $gridOld = $this->setDefaultGrid();
        $searchFieldOld = $gridOld->getSearchField();

        $ds = $this->getDataSourceAgg();
        $ds->addExtraFilters($gridOld->getDataSource()->getExtraFilter());

        $columnsGrid = $ds->mountColumns();

        $grid = new \Component\Grid\SearchGrid('agg', $ds, 'grid', $columnsGrid);
        $grid->setSearchField($searchFieldOld);
        $this->setGrid($grid);

        $grid->setCallInterfaceFunctions(FALSE);
        $div = $grid->onCreate();

        $views[] = $this->getHead();
        $views[] = $this->getBodyDiv($div);

        $this->append($views);
    }

    public function getGraphColor($item)
    {
        //not used in this case
        $item = null;
        return null;
    }

    /**
     * Make Graph/Chart
     *
     * @throws \UserException
     */
    public function makeGraph()
    {
        $chartType = Request::get('makeGraph') ? Request::get('makeGraph') : 'line';
        $ds = $this->addFiltersToDataSource($this->getDataSourceAgg());

        $chartCont = new HighChart('chartCont', $ds, $chartType);
        $chartCont->create();

        $grid = $this->setDefaultGrid();
        $searchField = new \Component\Grid\SearchField($grid, 'searchField');
        $searchField->onCreate();
        $this->setFocusOnFirstField();

        $views[] = $this->getHead();
        $views[] = $this->getBodyDiv(array($searchField, $chartCont));
    }

    public function getPopupAdd()
    {
        return $this->popupAdd || $this->getFormValue('popupAdd') || Get::get('popupAdd') || Get::get('popupAddRedirectPage');
    }

    public function setPopupAdd($popupAdd)
    {
        //disable popup forms if is not ajax
        if (\DataHandle\Server::getInstance()->isAjax())
        {
            $this->popupAdd = $popupAdd;
        }

        return $this;
    }

    /**
     * Montagem da tela de adicionar
     */
    public function adicionar()
    {
        $this->setFocusOnFirstField();

        if ($this->popupAdd)
        {
            return $this->getPopup();
        }
        else
        {
            $this->append($this->getHead());
            $this->append($this->getBodyDiv($this->mountFieldLayout()));
        }
    }

    public function getPopup()
    {
        $body[] = new \View\Div('popupHolder', $this->mountFieldLayout());

        $popupAddRedirectPage = Request::get('popupAddRedirectPage');

        //add popupadd to form, to make ir post corret
        $body[] = new \View\Input($this->getInputName('popupAdd'), \View\Input::TYPE_HIDDEN, 'popupAdd');
        $body[] = new \View\Input($this->getInputName('popupAddRedirectPage'), \View\Input::TYPE_HIDDEN, $popupAddRedirectPage);
        $body[] = new \View\Input('popupAddInputName', \View\Input::TYPE_HIDDEN, Request::get('popupAddInputName'));
        $body[] = new \View\Input('popupAddPageName', \View\Input::TYPE_HIDDEN, $this->getPageUrl());

        $buttons[] = $this->getTopButtons();

        $popup = new \View\Blend\Popup('popupAdicionar', $this->getTitle(), $body, $buttons, 'form ' . $this->getPageUrl());
        $popup->body->setId('bodyPopup');
        $popup->setIcon($this->icon);
        $popup->show();

        if ($popupAddRedirectPage)
        {
            $fechar = "p('$popupAddRedirectPage/cancelPopupAddSave');";
            $this->byId('btnVoltar')->click($fechar);
            $this->byId('btnVoltar')->removeAttr('data-form-changed-advice');
            $this->byId('btbClosePopup')->click($fechar);
        }
        else
        {
            $this->byId('btnVoltar')->click(\View\Blend\Popup::getJs('destroy') . ' p(\'' . $this->getPageUrl() . '\');');
        }
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
            return $this->getPopup();
        }
        else
        {
            $this->append($this->getHead());
            $this->append($this->getBodyDiv($this->mountFieldLayout()));
        }

        //remove the delete button if don't has permission
        if (!$this->verifyPermission('remover'))
        {
            $this->byId('btnRemover')->remove();
        }
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
     * Retorna o título da página
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
        return $this->getEvent() == self::EVENT_UPDATE;
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
            $btnSalvar->setTitle('Salva/Grava o registro atual no banco de dados!')->setDisabled();

            if ($this->isUpdate())
            {
                $this->floatingMenu = new \View\Blend\FloatingMenu('fm-action');
                $this->floatingMenu->addItem('btnRemover', 'trash', 'Remover ' . $this->getLcModelLabel(), 'remover', 'danger', 'Remove o registro atual do banco de dados!', TRUE);
                $this->floatingMenu->hide();

                $btnAction = new \View\Div('floating-menu', array(new \View\Ext\Icon('wrench'), new \View\Span(null, 'Ações', 'btn-label'), $this->floatingMenu), 'btn clean blend-floating-menu-holder');
                $btnAction->click('$("#fm-action").toggle(\'fast\');');

                $buttons[] = $btnAction;
            }

            $buttons[] = $btnVoltar = new \View\Ext\Button('btnVoltar', 'arrow-left', 'Voltar', 'history.back(1);');
            $btnVoltar->setTitle('Volta para a listagem!')->formChangedAdvice();
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
     * Solicita confirmação para remover
     */
    public function remover()
    {
        \App::dontChangeUrl();

        $footer[1] = new \View\Button('cancelar', array(new \View\Ext\Icon('arrow-left'), 'Não'), \View\Blend\Popup::getJs('destroy'), 'btn');

        $footer[0] = new \View\Button('confirmaRemocao', array(new \View\Ext\Icon('trash-o'), 'Sim'), 'confirmaExclusao', 'btn danger');
        $footer[0]->setAutoFocus();
        $footer[0]->focus();

        $popup = new \View\Blend\Popup('remocao', 'Confirmar remoção...', 'Confirma remoção do registro?', $footer);
        $popup->show();
    }

    /**
     * Remove registro
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

        $value = $this->getFormValue($pk);

        $model->setValue($pk, $value);

        try
        {
            $ok = $model->delete();

            if ($ok)
            {
                toast('Registro removido com sucesso!!');
                \App::redirect($this->getPageUrl(), TRUE);
            }
            else
            {
                toast('Problemas ao remover o registro!', 'danger');
            }
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

    public function defaultRedirect()
    {
        \App::dontChangeUrl();
        toast('OK! Gravado!', 'success');

        \App::redirect($this->getPageUrl());
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

    public function gridEdit()
    {
        \App::dontChangeUrl();
        $columnName = Request::get('columnName');
        $pkValue = str_replace('/', '', Request::get('v'));
        $this->setModelFromIdUrl();
        $model = $this->getModel();
        $column = $model->getColumn($columnName);

        $elementId = 'gridColumn-' . $columnName . '-' . trim($pkValue);

        $fieldLayout = $this->getFieldLayout();

        if (is_array($fieldLayout))
        {
            $fieldLayout = $fieldLayout[0];
        }

        $input = $fieldLayout->getInputField($column);
        $input->setValue($model->getValue($columnName));
        $input->addClass('full');
        $input->blur("e('saveGridEdit/$pkValue/?columnName={$columnName}');");

        $this->byId($elementId)->html($input)->attr('onclick', '');
    }

    public function saveGridEdit()
    {
        \App::dontChangeUrl();
        $columnName = Request::get('columnName');
        $pkValue = str_replace('/', '', Request::get('v'));
        $value = Request::get($columnName);
        $this->setModelFromIdUrl();
        $model = $this->getModel();

        $elementId = 'gridColumn-' . $columnName . '-' . trim($pkValue);

        $model->setValue($columnName, $value);

        $column = $model->getColumn($columnName);
        $pkColumn = $model->getPrimaryKey();

        $columns[$pkColumn . ""] = $pkColumn;
        $columns[$columnName] = $column;

        $model->update($columns);

        $td = $this->byId($elementId);
        $td->html('')->attr('onclick', "e('gridEdit/$pkValue/?columnName={$columnName}');");

        \App::refresh(TRUE);
    }

    /**
     * Return true if the search is filtred
     *
     * @return boolean
     */
    public function isFiltred()
    {
        return isset($_REQUEST['q']);
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
     * Createa fixed filter
     *
     * @param string $idColumn
     * @param array $options
     * @param mixed $defaultValue
     * @param string $allLabel
     * @return \View\Select
     */
    public function createFixedFilter($idColumn, $options, $defaultValue = '', $allLabel = 'Todos', $onlyFilter = false)
    {
        $column = $this->model->getColumn($idColumn);
        $label = $column ? $column->getLabel() : $idColumn;

        $grid = $this->getGrid();
        $nomeFiltro = 'filtro' . $idColumn;
        $valorFiltro = $this->getFixedFilterValue($nomeFiltro, $defaultValue);

        $campo = null;

        //create field only if needed
        if (!$onlyFilter)
        {
            //add suporte for constant values
            if ($options instanceof \Db\ConstantValues)
            {
                $options = $options->getArray();
            }

            //merge options
            $todos[''] = $allLabel ? $allLabel : 'Todos';
            $opcoesFinal = $todos + $options;

            $campo = new \View\Select($nomeFiltro, $opcoesFinal, $valorFiltro, 'span1');
            $campo->change("$('#buscar').click()");
            $campo->setTitle('Filtra por ' . lcfirst($label));

            //add filter to head
            $grid->getSearchField()->addExtraFilter($campo);
        }

        $firstLeter = '';
        $defaultQuery = Request::get('q');

        if ($defaultQuery)
        {
            $firstLeter = $defaultQuery[0];
        }

        //filter data
        $ds = $grid->getDataSource();

        //add support for @id parameter
        if ($valorFiltro || $valorFiltro === '0' && $firstLeter != '@')
        {
            $ds->addExtraFilter(new \Db\Cond($idColumn . '= ?', $valorFiltro));
        }

        return $campo;
    }

    /**
     * Create a fixed filter interval type
     *
     * @param string $idColumn
     * @param string $options
     * @param string $defaultValue
     * @param string $allLabel
     * @return \View\Select
     */
    public function createFixedIntervalFilter($idColumn, $defaultValueBegin = '', $defaultValueEnd = '', $onlyFilter = false)
    {
        $column = $this->model->getColumn($idColumn);
        $label = $column ? $column->getLabel() : $idColumn;

        $grid = $this->getGrid();
        $nomeFiltroInicio = 'filtroInicio' . $idColumn;
        $nomeFiltroFim = 'filtrofim' . $idColumn;

        $valorFiltroInicio = new \Type\Date($this->getFixedFilterValue($nomeFiltroInicio, $defaultValueBegin));
        $valorFiltroFim = new \Type\Date($this->getFixedFilterValue($nomeFiltroFim, $defaultValueEnd));

        $campo = null;

        //create field only if needed
        if (!$onlyFilter)
        {
            //cria campo start
            $campo = new \View\Ext\DateInput($nomeFiltroInicio, $valorFiltroInicio, 'span1');
            $campo->change("$('#buscar').click()");
            $campo->setTitle('Filtra por ' . lcfirst($label));
            //add field to head
            $grid->getSearchField()->addExtraFilter($campo);

            //cria campo start
            $campo2 = new \View\Ext\DateInput($nomeFiltroFim, $valorFiltroFim, 'span1');
            $campo2->change("$('#buscar').click()");
            $campo2->setTitle('Filtra por ' . lcfirst($label));
            //add field to head
            $grid->getSearchField()->addExtraFilter($campo2);
        }

        //filter data
        $ds = $grid->getDataSource();

        $firstLeter = '';
        $defaultQuery = Request::get('q');

        if ($defaultQuery)
        {
            $firstLeter = $defaultQuery[0];
        }

        if ($firstLeter != '@')
        {
            if ($valorFiltroInicio . '')
            {
                $ds->addExtraFilter(new \Db\Cond('date(' . $idColumn . ') >= ?', $valorFiltroInicio->toDb()));
            }

            if ($valorFiltroFim . '')
            {
                $ds->addExtraFilter(new \Db\Cond('date(' . $idColumn . ') <= ?', $valorFiltroFim->toDb()));
            }
        }

        return $campo;
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

}
