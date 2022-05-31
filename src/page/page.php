<?php

namespace Page;

use DataHandle\Request;
use DataHandle\Config;
use DataHandle\Session;

/**
 * Simple page
 */
class Page extends \View\Layout
{

    /**
     * Listagem de grids da páginas
     *
     * @var array
     */
    protected $grid;

    /**
     * Define the icon of the page
     *
     * @var mixed
     */
    protected $icon;

    /**
     * Construct the page
     *
     * @return mixed
     */
    public function __construct()
    {
        parent::__construct();

        \View\View::setDom($this);

        $fields[] = $this->callEvent();

        if (\App::isUrlChanged())
        {
            \App::addJs("$('body').attr('data-page-url','{$this->getPageUrl()}');");
        }

        return $fields;
    }

    /**
     * List all acl events
     *
     * @return array
     */
    public static function listAclEvents()
    {
        $events['listar'] = 'Listar';

        return $events;
    }

    /**
     * Verify if event is acl controlled
     *
     * @param string $event
     * @return bool
     */
    public function isEventAcl($event)
    {
        $class = get_class($this);
        return array_key_exists($event, $class::listAclEvents());
    }

    /**
     * Loads and parse html layout file.
     * Function similar to Android devel.
     * @deprecated since version 03/06/2014
     *
     * @param String $layoutPath
     */
    public function setContentView($layoutPath)
    {
        $this->setLayoutFile($layoutPath);
    }

    /**
     * Return the icon
     *
     * @return mixed
     */
    public function getIcon()
    {
        $icon = $this->icon;

        if (is_string($icon) && !\View\View::isHtml($icon))
        {
            $icon = new \View\Ext\Icon($icon);
        }

        return $icon;
    }

    /**
     * Define icon
     *
     * @param mixed $icon
     * @return \Page\Page
     */
    public function setIcon($icon)
    {
        $this->icon = $icon;
        return $this;
    }

    /**
     * Return the default url for search
     *
     * @return string
     */
    public function getSearchUrl()
    {
        return $this->getPageUrl();
    }

    /**
     * Faz validação dos registros
     *
     * @param \Db\Model $model
     * @return boolean
     */
    public function validateModel(\Db\Model $model)
    {
        \View\View::removeAllInvalidate();
        $ok = true;
        $arrayErrorMsg = NULL;
        $errors = $model->validate();

        if (is_array($errors) && count($errors) > 0)
        {
            $json = array();

            foreach ($errors as $field => $errorMsg)
            {
                if (is_array($errorMsg))
                {
                    $label = $model->getColumn($field)->getLabel();

                    foreach ($errorMsg as $msg)
                    {
                        $arrayErrorMsg[] = $msg;
                        $message = \View\Script::treatStringToJs($msg);
                        $this->byId($field)->setInvalid(true, $message);
                    }

                    $stdClass = new \stdClass();
                    $stdClass->name = $field;
                    $stdClass->label = $label;
                    $stdClass->messages = $errorMsg;

                    $json[] = $stdClass;
                }

                $ok = false;
            }

            $json = \Disk\Json::encode($json);
            \App::addJs("showValidateErrors({$json});");
        }

        return $ok;
    }

    /**
     * Make all fields readonlyt
     */
    public function makeAllReadOnly()
    {
        $groups[] = $this->getElementsByTagName('input');
        $groups[] = $this->getElementsByTagName('select');
        $groups[] = $this->getElementsByTagName('textarea');

        foreach ($groups as $elements)
        {
            foreach ($elements as $element)
            {
                if ($element instanceof \View\View)
                {
                    $element->setReadOnly(TRUE);
                }
                else
                {
                    $element->setAttribute('readonly', 'readonly');
                }
            }
        }
    }

    public function getTopButtons()
    {
        return NULL;
    }

    /**
     * Retorna a div principal da página
     *
     * @param array $fields
     *
     * @return \View\Div
     */
    protected function getBodyDiv($fields)
    {
        return new \View\Div('divLegal', $fields, $this->getEvent() . ' page-' . $this->getPageUrl() . ' makePopupFade clearfix');
    }

    public function getTopButtonsSearch()
    {
        return NULL;
    }

    public function isSearch()
    {
        return TRUE;
    }

    /**
     * Return the form title element
     *
     * @return \View\Span
     */
    public function getFormTitle()
    {
        $searchTitle = Request::get('search-title');
        return new \View\Span('extraTitle', array($this->getIcon(), $this->getTitle() . ' ' . $searchTitle));
    }

    /**
     * Return the page head
     *
     * @return \View\Div
     */
    public function getHead()
    {
        $title = $this->getFormTitle();
        $head[] = new \View\H1('formTitle', $title, 'formTitle');
        $head[] = new \View\Div('btnGroup', $this->getTopButtons($this->getEvent()), 'btnGroup clearfix');

        return new \View\Div('pageHead', $head, 'makePopupFade');
    }

    /**
     * Save list item
     */
    public function saveListItem()
    {
        \App::dontChangeUrl();

        $question[] = 'Utilize algo que tenha significado!';
        $question[] = new \View\Br();
        $question[] = new \View\Br();
        $question[] = $input = new \View\Input('saveList[title]', \View\Input::TYPE_TEXT, null, 'fullWidth');
        $input->onPressEnter("$('#ok').click()");

        \View\Blend\Popup::prompt('Defina o título da pesquisa', $question, 'saveListItemConfirm')->show();

        \App::addJs('setTimeout( function(){$("#saveListtitle").focus();},200)');
    }

    /**
     * Get search saved list
     *
     * @return \Filter\SavedList
     */
    public function getSavedList()
    {
        return new \Filter\SavedList();
    }

    /**
     * Save list item confirm
     */
    public function saveListItemConfirm()
    {
        \App::dontChangeUrl();
        $saveList = $this->getSavedList();
        $request = (Object) Request::get('saveList');
        $item = $saveList->save($this->getPageUrl(), \Filter\SavedList::mountUrl(), $request->title);

        if ($item)
        {
            toast('Pesquisa salva!');
            \App::redirect($this->getPageUrl() . '/?' . $item->url . '&savedList=' . $item->id);
        }
    }

    /**
     * Delete list item
     */
    public function deleteListItem()
    {
        \App::dontChangeUrl();
        $saveList = $this->getSavedList();
        $json = $saveList->getObject();
        $id = Request::get('savedList');

        if (isset($json->$id))
        {
            $item = $json->$id;
            $title = $item->title;
            $content[] = 'Comfirmação remoção da lista <strong>' . $title . '</strong>?';
            $content[] = new \View\Input('savedList', 'hidden', $id, '');
            \View\Blend\Popup::prompt('Confirmação', $content, 'deleteListItemConfirm')->show();
        }
        else
        {
            toast('É necessário selecionar alguma pesquisa para fazer a remoção!');
        }
    }

    /**
     * Delete list item confirm
     */
    public function deleteListItemConfirm()
    {
        \App::dontChangeUrl();

        $saveList = $this->getSavedList();
        $ok = $saveList->delete(Request::get('savedList'));

        if ($ok)
        {
            toast('Pesquisa removida!');
            \App::redirect($this->getPageUrl());
        }
    }

    /**
     * Return the current grid
     *
     * @return \Component\Grid\Grid
     */
    public function getGrid()
    {
        if (is_null($this->grid))
        {
            return $this->setDefaultGrid();
        }

        return $this->grid;
    }

    public function setGrid(\Component\Grid\Grid $grid)
    {
        $this->grid = $grid;

        return $this;
    }

    public function isGridGrouped()
    {
        return Request::get('grid-groupby-field') ? true : false;
    }

    public function isGridUserAddedColumns()
    {
        return \DataHandle\Request::get('grid-addcolumn-field') ? true : false;
    }

    public function isGridCustomized()
    {
        return $this->isGridGrouped() || $this->isGridUserAddedColumns();
    }

    /**
     * Montagem de tela de adicionar
     */
    public function listar()
    {
        $this->setDefaultGrid();
        $grid = $this->getGrid();

        //to avoid problemns when grid does not exists, really need a remake
        if (!$grid)
        {
            return $this->onCreate();
        }

        $this->addFiltersToDataSource($grid->getDataSource());
        $views[] = $this->getHead();
        $views[] = $this->getBodyDiv([new \View\Div('content-pre'), $grid]);

        $this->append($views);
        $this->byId('q')->focus();
    }

    public function gridExportData()
    {
        \App::dontChangeUrl();

        $grid = $this->getGrid();

        return $grid->gridExportData();
    }

    /**
     * Return the file from report columns
     *
     * @return \Disk\File
     */
    public function getReportColumnsFile()
    {
        $idUser = Session::get('user') ? Session::get('user') . '/' : '';
        $fileReportColumns = \Disk\File::getFromStorage($idUser . 'report-columns-' . $this->getPageUrl() . '.json');

        return $fileReportColumns;
    }

    /**
     * Export file from grid
     *
     * @throws \Exception
     */
    public function exportGridFile()
    {
        \App::dontChangeUrl();
        $this->setDefaultGrid();
        \App::setResponse('messageContain');

        $groupType = Request::get('group-type');

        if ($groupType)
        {
            $grid = $this->getGroupGrid();
        }
        else
        {
            //evoid memory break
            $grid = $this->getGrid();
            $ds = $grid->getDataSource();
            $this->addFiltersToDataSource($ds);
            $ds->setPage(NULL)->setLimit(NULL);
            $grid->setDataSource($ds);

            if ($grid->getCallInterfaceFunctions() && $this instanceof \Page\BeforeGridCreateRow)
            {
                $data = $ds->getData();

                if (isIterable($data))
                {
                    foreach ($data as $item)
                    {
                        $this->beforeGridCreateRow($item, null, NULL);
                    }
                }
            }
        }

        return $grid->exportGridFile();
    }

    /**
     * Add search filters to datasoruce
     * @param \DataSource\DataSource $dataSource
     * @return \DataSource\DataSource $dataSource
     */
    public function addFiltersToDataSource(\DataSource\DataSource $dataSource)
    {
        return \Component\Grid\Grid::addFiltersToDataSource($dataSource);
    }

    /**
     * Create a new grid
     *
     * @return \Component\Grid\SearchGrid
     */
    public function createGrid()
    {
        $grid = new \Component\Grid\SearchGrid('grid' . $this->getModel()->getName(), $this->getDataSource());
        $grid->setActions($this->setDefaultActions());
        $this->setGrid($grid);

        return $grid;
    }

    /**
     * Create a the default grid for the page
     *
     * @return \Component\Grid\SearchGrid
     */
    public function setDefaultGrid()
    {
        //if grid allready exists don't create it again, make it to json serialization work
        if ($this->grid)
        {
            return $this->grid;
        }

        if (method_exists($this, 'getModel'))
        {
            $grid = $this->createGrid();

            $events = [];
            $events[] = 'listar';
            $events[] = 'gridExportData';
            $events[] = 'exportGridFile';

            if (in_array($this->getEvent(), $events))
            {
                $gridGroupBy = Request::get('grid-groupby-field');
                $extraColumns = Request::get('grid-addcolumn-field');

                if (is_array($gridGroupBy))
                {
                    $dataSource = $grid->getDataSource();
                    $dataSource->getColumns(); //forece column mount
                    $dataSource = $this->getGroupedDataSource();
                    $data = \Component\Grid\GroupHelper::parseData($this, $dataSource);

                    $dataSource->setCount($data->count());
                }
                else if (is_array($extraColumns))
                {
                    $userDataSource = $this->getUserDefinedDatasource();

                    if ($userDataSource)
                    {
                        $grid->setDataSource($userDataSource);
                    }
                }
            }

            return $grid;
        }
    }

    /**
     * Create/Return the default datasource
     * This method can be used to customize the default datasource
     *
     * @return \DataSource\Vector
     */
    public function getDatasource()
    {
        return new \DataSource\Vector();
    }

    /**
     * Create/Return the user defined datasource
     * This method can be used to customize the user datasource
     *
     * @return \DataSource\QueryBuilder
     */
    public function getUserDefinedDatasource()
    {
        return \Component\Grid\GroupHelper::getUserDefinedDataSource();
    }

    /**
     * Create/Return the grouped datasource
     * This method can be used to customize the grouped datasource
     *
     * @return \DataSource\QueryBuilder datasource
     */
    public function getGroupedDataSource()
    {
        return \Component\Grid\GroupHelper::getGroupedDataSource();
    }

    /**
     * Set the default column groups for grid groupment
     *
     * @param array $columnGroup
     */
    public function setDefaultGroups($columnGroup)
    {
        return $columnGroup;
    }

    public function salvar($model = NULL, $defaultRedirect = TRUE)
    {
        \App::dontChangeUrl();
        \App::setResponse('null');

        //valida dados, caso tenha retornado um controle quer dizer que teve problema
        if (!$this->validateModel($model))
        {
            \App::dontChangeUrl();
            return FALSE;
        }

        //controla a transação pois o save pode fazer várias operações
        \Db\Conn::getInstance()->beginTransaction();
        $result = $model->save();
        \Db\Conn::getInstance()->commit();

        if (!$result)
        {
            toast('Problemas ao salvar registro!', 'danger');
            return $result;
        }
        else
        {
            if ($defaultRedirect)
            {
                $this->defaultRedirect();
            }

            return $result;
        }
    }

    /**
     * Default redirect after save
     */
    public function defaultRedirect($mensagem = 'OK! Gravado!', $type = 'success')
    {
        \App::dontChangeUrl();
        toast($mensagem, $type);
        \App::redirect($this->getSearchUrl(), TRUE);
    }

    public function saveModelDialog()
    {
        $modelName = Request::get('model');
        $gridName = Request::get('gridName');
        $model = new $modelName();
        $model->setData(Request::getInstance());
        $ok = $this->salvar($model, false);

        if ($ok)
        {
            if (isset($gridName))
            {
                $grid = $this->getGrid($gridName);
                $grid->createTable();
                $this->removeChild($grid);

                \App::setResponse($grid->getId());
            }
        }
    }

    /**
     * Create a constainer with label
     *
     * @param string $label
     * @param \View\View $view
     * @param string $class
     *
     * @return \View\Div
     */
    public function getContainer($label, $view, $class = NULL)
    {
        return self::createContainer($label, $view, $class);
    }

    public static function createContainer($label, $view, $class = NULL)
    {
        if ($view instanceof \Component\Component)
        {
            $view = $view->onCreate();
        }

        $label = new \View\Label('label_' . $view->getId(), $view->getId(), $label, 'field-label');
        $contain = new \View\Div('contain_' . $view->getId(), array($label, $view), 'field-contain ' . $class);

        return $contain;
    }

    /**
     * Upload a file
     *
     * @return \Disk\File
     */
    public function fileUpload($uploadFile = NULL)
    {
        \App::dontChangeUrl();
        $files = \DataHandle\Files::getInstance();
        $return = [];

        foreach ($files as $key => $file)
        {
            if (isset($file))
            {
                $return[$key] = $this->upload($file, $uploadFile);
            }
        }

        if (isCountable($return) && count($return) == 1)
        {
            $return = array_pop($return);
        }

        return $return;
    }

    public function upload($file = NULL, $uploadFile = NULL)
    {
        if (!$file)
        {
            return;
        }

        $fileUpload = new \Disk\FileUpload($file);
        $fileUpload->verifyExtension(array('php', 'html', 'js'), TRUE);

        $uploadFile = $uploadFile ? $uploadFile : new \Disk\Media($fileUpload->getUploadFileName());

        if ($fileUpload->upload($uploadFile))
        {
            return $uploadFile;
        }

        return NULL;
    }

    /**
     * Set focus on first field
     *
     * @return false
     */
    public function setFocusOnFirstField()
    {
        \App::addJs('setFocusOnFirstField()');

        return false;
    }

    /**
     * Bind var func execution on key press.
     *
     * @param string $key Ex. ['F5','Ctrl+Alt+S']
     * @param string $func Ex. function() { alert('this is my function !'); }
     */
    public static function addShortcut($key, $func)
    {
        \App::addJs("addShortcut('$key',$func)");
    }

    /**
     * Unbind function execution on $key press.
     * @param type $key
     */
    public static function removeShortcut($key)
    {
        \App::addJs("removeShortcut('$key')");
    }

    public function updateGrid($id)
    {
        //add support for old \Grid and new \Component\Grid
        $gridClass = '\Grid\\' . $id;

        if (!class_exists($gridClass))
        {
            $gridClass = '\Component\Grid\\' . $id;
        }

        //FIXME this started to be a mess
        if (!class_exists($gridClass))
        {
            $gridClass = $id;
        }

        $grid = new $gridClass;
        $table = $grid->createTableInner();

        //add support for open accordion
        if ($table instanceof \View\Ext\Accordion)
        {
            $table->open();
        }

        $element = new \View\Div(\View\View::REPLACE_SHARP . substr($gridClass, 1));
        $element->setOutputJs(TRUE);
        //remove do dom para não reaparecer
        //$element->remove();
        $element->parentNode->removeChild($element);
        $element->html($table);

        \App::setResponse('other-content-to-avoid-problem', null);
    }

    /**
     * Add advanced filter
     * Called from grid popup
     *
     * @return boolean
     */
    public function addAdvancedFilter()
    {
        \App::dontChangeUrl();
        \App::setResponse('NULL'); //for grid
        //support columns with description in name
        $value = str_replace('Description', '', Request::get('advancedFiltersList'));
        $grid = $this->setDefaultGrid();

        if (!$grid instanceof \Component\Grid\Grid)
        {
            return false;
        }

        $filter = $grid->getFilter($value);

        if (!$filter->getFilterLabel())
        {
            toast('Impossível encontrar filtro selecionado: ' . $value, 'danger');
            return;
        }

        $this->byId('advancedFiltersList')->val('');

        //filter is allready on page
        if ($filter->getFilterValue(0))
        {
            \App::addJs("setTimeout(function(){ $('#{$filter->getFilterName()}Filter .addFilter').click() }, 300);");
            return;
        }

        $input = $filter->getInput();
        //remove the filter if exists
        \App::addJs("$('#{$input->getId()}').remove();");
        $this->byId('filters-holder')->append($input);
        \App::addJs("$('.filterCondition').change();"); //call js change
        //put focus on input field
        \App::addJs("$('#{$input->getId()}').find('.filterInput').focus();");
        return $this;
    }

    /**
     * Return main div "#divLegal"
     *
     * @return \View\View
     */
    public function getMainDiv()
    {
        return $this->byId('divLegal');
    }

    /**
     * Return main form "#content"
     *
     * @return \View\View
     */
    public function getMainForm()
    {
        return $this->byId('content', '\View\Div');
    }

    /**
     * Add tool tip to element
     *
     * @param string $selector
     * @param string $message
     */
    public function addToolTip($selector, $message)
    {
        \App::addJs("toolTip('{$selector}', '{$message}');");
    }

    public function gridGroupAddColumn()
    {
        \App::dontChangeUrl();
        $this->getGrid()->gridGroupAddColumn();
    }

    public function gridGroupAddGroup()
    {
        \App::dontChangeUrl();
        $this->getGrid()->popupAddGroup();
    }

    public function gridGroupAddAggr()
    {
        \App::dontChangeUrl();
        $this->getGrid()->popupAddAggr();
    }

    public function gridGroupCreateColumns()
    {
        \App::dontChangeUrl();
        $this->byId('tab-column')->html($this->getGrid()->createColumns());
        $this->byId('tab-columnLabel')->attr('onclick', "return selectTab('tab-column');");
        \App::addJs("return selectTab('tab-column');");
    }

    public function gridGroupGroupment()
    {
        \App::dontChangeUrl();
        $this->byId('tab-group')->html($this->getGrid()->createGroupment());
        $this->byId('tab-groupLabel')->attr('onclick', "return selectTab('tab-group');");
        \App::addJs("return selectTab('tab-group');");
    }

}
