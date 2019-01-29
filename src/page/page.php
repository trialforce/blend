<?php

namespace Page;

use DataHandle\Get;
use DataHandle\Request;
use DataHandle\Config;
use DataHandle\Session;

/**
 * Simple page
 */
class Page extends \View\Layout
{

    protected $popupAdd = FALSE;

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

        return $fields;
    }

    public function getPopupAdd()
    {
        return $this->popupAdd || Get::get('popupAdd') || Get::get('popupAddRedirectPage');
    }

    public function setPopupAdd($popupAdd)
    {
        //disable popup forms if is not ajax
        //if (\DataHandle\Server::getInstance()->isAjax())
        //{
        $this->popupAdd = $popupAdd;
        //}

        return $this;
    }

    /**
     * Return the current event
     *
     * @return string
     */
    public function getEvent()
    {
        $event = Request::get('e');

        if (!$event)
        {
            $event = Request::get('q') ? 'listar' : 'oncreate';
        }

        return $event;
    }

    /**
     * Evita cadastro de eventos em demasia.
     *
     * @param string $event
     * @return string
     */
    public function parseEvent($event)
    {
        //ajusta alguns eventos para evitar cadastro de em demasia
        $replace['oncreate'] = 'listar';
        $replace['confirmaExclusao'] = 'remover';
        $replace['salvar'] = 'adicionar';

        return str_replace(array_keys($replace), array_values($replace), $event);
    }

    /**
     * Executa/chama o evento atual
     *
     * @return mixed
     */
    public function callEvent()
    {
        $event = $this->getEvent();

        if (!$event)
        {
            return false;
        }

        $canDo = $this->verifyPermission($this->parseEvent($event));

        if (!$canDo)
        {
            throw new \UserException('Sem permissão para acessar evento <strong>' . ucfirst($event) . '</strong> na página <strong>' . ucfirst($this->getPageUrl()) . '</strong>.');
        }

        if (method_exists($this, $event))
        {
            return $this->$event();
        }
    }

    /**
     * Verify permission to event
     *
     * @param string $event
     * @return boolean
     */
    public function verifyPermission($event)
    {
        //not used is this case
        $event = NULL;
        return true;
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
     * Return the url of the page
     *
     * @return string
     */
    public function getPageUrl()
    {
        $class = str_replace('\\', '-', get_class($this));
        $class = str_replace(array('Page\\', 'page\\', 'page-', 'Page-'), '', $class);

        return strtolower($class);
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
        $arrayErrorMsg = NULL;
        $errors = $model->validate();

        if (count($errors) > 0)
        {
            $campos = '';
            foreach ($errors as $field => $errorMsg)
            {
                if (is_array($errorMsg))
                {
                    foreach ($errorMsg as $msg)
                    {
                        $arrayErrorMsg[] = $msg;
                        $message = \View\Script::treatStringToJs($msg);
                        $this->byId($field)->setInvalid(true, $message);
                        $campos .= $model->getColumn($field)->getLabel() . ' | ' . $message . '<br />';
                    }
                }
            }

            if ($arrayErrorMsg)
            {
                if (empty($campos))
                {
                    toast('Verifique o preenchimento dos campos.', 'danger');
                }
                else
                {
                    toast('Verifique o preenchimento dos campos. <br /><br />' . $campos, 'danger');
                }

                return FALSE;
            }
        }

        return TRUE;
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
        return new \View\Div('divLegal', $fields, $this->getEvent() . ' ' . $this->getPageUrl() . ' makePopupFade clearfix');
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
     * Return save list fields
     *
     * @return \View\Ext\Button
     */
    public function getSaveListFields()
    {
        $pageUrl = $this->getPageUrl();
        $view[] = $savedList = new \View\Select('savedList', $this->getSavedListOptions(), Request::get('savedList'), 'savedList');
        $savedList->change("var url = $(this).find('option:selected').data('url'); if (typeof url !== 'undefined'){ window.location = url } else { window.location='{$pageUrl}'}");

        $params['orderBy'] = Request::get('orderBy');
        $params['orderWay'] = Request::get('orderWay');

        $url = http_build_query($params);

        $view[] = new \View\Ext\Button('saveListItem', 'save', '', "return g('{$this->getPageUrl()}/saveListItem/','{$url}'+ '&' + $('form').serialize())", 'add');
        $view[] = new \View\Ext\Button('deleteListItem', 'trash', '', 'deleteListItem', 'add');

        return $view;
    }

    /**
     * Return the page head
     *
     * @return \View\Div
     */
    public function getHead()
    {
        $title = new \View\Span('extraTitle', array($this->getIcon(), $this->getTitle()));
        $view = null;

        if ($this->isSearch())
        {
            $view = $this->getSaveListFields();
        }

        $head[] = new \View\H1('formTitle', $title, 'formTitle');

        if (is_array($view))
        {
            $head[] = new \View\Div('savedListGroup', $view, 'savedListGroup');
        }

        $head[] = new \View\Div('btnGroup', $this->getTopButtons($this->getEvent()), 'btnGroup clearfix');

        return new \View\Div('pageHead', $head, 'makePopupFade');
    }

    /**
     * Return saved list file
     *
     * @return \Disk\File
     */
    public function getSavedListFile()
    {
        return new \Disk\File(\Disk\Media::getMediaPath() . 'savedlist.json');
    }

    /**
     *
     * @return stdClass
     */
    public function getSavedListObject()
    {
        $file = $this->getSavedListFile();

        $folder = $file->getFolder();

        if ($folder->isWritable() && !$file->exists())
        {
            $file->save('');
        }

        if (!$file->exists())
        {
            throw new \Exception('Impossível criar arquivo de pesquisas verifique permissão no arquivo ' . $file->getPath() . ' ');
        }

        $file->load();
        return json_decode($file->getContent());
    }

    /**
     *
     * @return array of  \View\Option
     */
    public function getSavedListOptions()
    {
        $json = $this->getSavedListObject();
        $list = NULL;
        $page = $this->getPageUrl();

        if (is_object($json) && count($json) > 0)
        {
            foreach ($json as $id => $item)
            {
                if ($item->page == $page)
                {
                    $list[] = $option = new \View\Option($id, $item->title);
                    $option->setData('url', $item->page . '/?' . $item->url . '&savedList=' . $id);
                }
            }
        }

        return $list;
    }

    /**
     * Save list item
     */
    public function saveListItem()
    {
        \App::dontChangeUrl();

        $parts = $_GET;
        unset($parts['p']);
        unset($parts['e']);
        unset($parts['v']);
        unset($parts['formChanged']);
        unset($parts['selectFilters']);
        unset($parts['selectGroups']);
        unset($parts['_']);
        unset($parts['paginationLimit']);
        unset($parts['savedList']);

        if (strlen($parts['q']) == 0)
        {
            unset($parts['q']);
        }

        if (isset($parts['makeGraph']) && strlen($parts['makeGraph']) == 0)
        {
            unset($parts['makeGraph']);
        }

        $url = http_build_query($parts);

        $question[] = new \View\Input('saveList[url]', \View\Input::TYPE_HIDDEN, $url);
        $question[] = 'Utilize algo que tenha significado!';
        $question[] = $input = new \View\Input('saveList[title]', \View\Input::TYPE_TEXT, '');
        $input->onPressEnter("$('#ok').click()");

        \View\Blend\Popup::prompt('Defina o título da consulta', $question, 'saveListItemConfirm')->show();

        \App::addJs("$('#saveList\\\\[title\\\\]').focus().val( $('#savedList option:selected').html() )");
    }

    /**
     * Save list item confirm
     */
    public function saveListItemConfirm()
    {
        \App::dontChangeUrl();
        $file = $this->getSavedListFile();
        $list = $this->getSavedListObject();

        //avoid empty object
        if (!$list instanceof \stdClass)
        {
            $list = new \stdClass();
        }

        $saveList = (Object) Request::get('saveList');
        $saveList->page = $this->getPageUrl();
        $id = \Type\Text::get($saveList->title)->toFile() . '';

        $list->$id = $saveList;

        if (defined('JSON_PRETTY_PRINT'))
        {
            $json = json_encode($list, JSON_PRETTY_PRINT);
        }
        else
        {
            $json = json_encode($list);
        }

        if ($file->isWritable())
        {
            $file->save($json);

            toast('Pesquisa salva!');
            \App::redirect($this->getPageUrl() . '/?' . $saveList->url . '&savedList=' . $id);
        }
        else
        {
            throw new \Exception('Impossível salvar pesquisa verifique permissão no arquivo ' . $file->getPath() . ' ');
        }
    }

    /**
     * Delete list item
     */
    public function deleteListItem()
    {
        $json = $this->getSavedListObject();
        $id = Request::get('savedList');

        if (isset($json->$id))
        {
            $item = $json->$id;
            $title = $item->title;
            \View\Blend\Popup::prompt('Confirmação', 'Comfirmação remoção da lista <strong>' . $title . '</strong>?', 'deleteListItemConfirm')->show();
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

        $file = $this->getSavedListFile();
        $list = $this->getSavedListObject();

        $id = Request::get('savedList');

        unset($list->$id);

        if (defined('JSON_PRETTY_PRINT'))
        {
            $json = json_encode($list, JSON_PRETTY_PRINT);
        }
        else
        {
            $json = json_encode($list);
        }

        if ($file->isWritable())
        {
            $file->save($json);

            toast('Pesquisa removida!');
            \App::redirect($this->getPageUrl());
        }
        else
        {
            throw new \Exception('Impossível remover pesquisa verifique permissão!');
        }
    }

    /**
     * Return the current grid
     *
     * @return \Component\Grid
     */
    public function getGrid()
    {
        if (is_null($this->grid))
        {
            $this->setDefaultGrid();
        }

        return $this->grid;
    }

    public function setGrid(\Component\Grid\Grid $grid)
    {
        $this->grid = $grid;

        return $this;
    }

    /**
     * Montagem de tela de adicionar
     */
    public function listar()
    {
        $this->setFocusOnFirstField();
        $groupType = Request::get('group-type');

        if (is_array($groupType) && count($groupType) > 1)
        {
            return $this->createGroupGrid();
        }

        $this->setDefaultGrid();
        $grid = $this->getGrid(Request::get('stateId'));

        //to avoid problemns when grid does not exists, really need a remake
        if (!$grid)
        {
            return $this->onCreate();
        }

        $this->addFiltersToDataSource($grid->getDataSource());
        $div = $grid->onCreate();

        $views[] = $this->getHead();
        $views[] = $this->getBodyDiv($div);

        $this->append($views);

        $this->byId('q')->focus();
    }

    public function createGroupGrid()
    {

    }

    public function gridExportData()
    {
        \App::dontChangeUrl();

        $groupType = Request::get('group-type');

        if ($groupType)
        {
            $grid = $this->getGroupGrid();
        }
        else
        {
            $grid = $this->getGrid();
        }

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

    public function setDefaultGrid()
    {
        //if grid allready exists don't create it again, make it to json serialization work
        if ($this->grid)
        {
            return $this->grid;
        }

        if (method_exists($this, 'getModel'))
        {
            $grid = new \Component\Grid\SearchGrid('grid' . $this->getModel()->getName(), $this->getDataSource());
            $this->setGrid($grid);

            return $grid;
        }
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
            toast('Problemas ao salvar registro!');
            return $result;
        }
        else
        {
            $this->byId('formChanged')->val('');
            //caso padrao
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
        if ($this->getPopupAdd())
        {
            \View\Blend\Popup::delete();
        }
        else
        {
            \App::dontChangeUrl();
            toast($mensagem, $type);
            \App::redirect($this->getSearchUrl(), TRUE);
        }
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
        if ($view instanceof \Component\Component)
        {
            $view = $view->onCreate();
        }

        $label = new \View\Label('label_' . $view->getId(), $view->getId(), $label, 'field-label');
        $contain = new \View\Div('contain_' . $view->getId(), array($label, $view), 'field-contain ' . $class);

        return $contain;
    }

    /**
     * Add media from niceditor
     */
    public function addMedia()
    {
        $body[] = $upload = new \View\Input('mediaUpload', \View\Input::TYPE_FILE);
        $upload->attr('multiple', 'multiple');
        $upload->change("fileUpload('{$this->getPageUrl()}/mediaUpload')");

        $body[] = new \View\Div('mediaContainer', $this->listMedia());
        $body[] = new \View\Div('selectedContainer', NULL, 'clearfix');

        $footer[] = $btnAddMedia = new \View\Input('btnAddMedia', \View\Input::TYPE_SUBMIT, 'Adicionar media', 'btn primary fl');
        $btnAddMedia->click('passThis.submit(document.getElementById(\'btnAddMedia\')); ' . \View\Blend\Popup::getJs('destroy', 'id'));

        //$footer[] = $btnExcluirFoto = new \View\Ext\Button( 'deletaFoto', '', 'Excluir Foto', 'removerFoto', 'btn danger', '' );

        $footer = new \View\Form(NULL, $footer, NULL, NULL);
        $footer->attr('onsubmit', 'return false;');

        $body[] = $footer;

        $popup = new \View\Blend\Popup('id', 'Adicionar media', $body, NULL, 'big');
        $popup->show();

        //remove default nic panel
        \App::addJs('$(\'.nicEdit-pane\').parent().hide();');
        \App::setPushState(Request::get('p'));
    }

    /**
     * Lista all media filés
     *
     * @return \View\Div
     */
    public function listMedia()
    {
        $folder = \Disk\Media::getMediaFolder();
        $files = $folder->listFiles();

        $i = 0;

        $images = array();

        foreach ($files as $file)
        {
            //convett to media object
            $file = new \Disk\Media($file);
            $isFile = $file->isFile();

            if ($file->isImage())
            {
                $view = new \View\Img('img' . $i, $file->getUrl(), NULL, '100', $file->getPath());
            }
            else if ($isFile)
            {
                $view = new \View\Span('file' . $i, $file->getExtension());
                $view->setTitle($file->getBasename());
            }

            if ($isFile)
            {
                $images[] = $imgCont = new \View\Div('imgCont' . $i, $view);
                $basename = $file->getBasename();
                $imgCont->click("e('selectMedia/{$basename}');");
                $i++;
            }
        }

        return $images;
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
        $return = NULL;

        foreach ($files as $key => $file)
        {
            if (isset($file))
            {
                $return[$key] = $this->upload($file, $uploadFile);
            }
        }

        if (count($return) == 1)
        {
            $return = array_pop($return);
        }

        return $return;
    }

    public function upload($file, $uploadFile = NULL)
    {
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
     * Make upload from media gallery
     *
     * @return \Disk\File
     */
    public function mediaUpload()
    {
        $file = $this->fileUpload();

        if ($file)
        {
            $this->byId('mediaContainer')->html($this->listMedia());
        }

        if (!is_array($file))
        {
            Request::set('v', $file . '');
            $this->selectMedia();
            \App::addJs("$('#btnAddMedia').click();");
        }

        return $file;
    }

    public function convert(\Disk\Media $uploadFile)
    {
        if (\Media\ImageMagick::isInstalled() && $uploadFile->getExtension() == \Media\Image::EXT_PSD)
        {
            $image = new \Media\Image($uploadFile, TRUE);
            $image->setExtension(\Media\Image::EXT_PNG);
            $image->export($image);
        }
    }

    /**
     * Function called when media is selected
     */
    public function selectMedia()
    {
        \App::dontChangeUrl();
        $file = new \Disk\Media(Request::get('v'));

        $link = new \View\A('selectedMedia', $file->getBasename(), $file->getUrl(), \View\A::TARGET_BLANK);
        $view[] = $this->getContainer('Caminho', $link);

        $altInput = new \View\Input('alt', 'text', $file->getFriendName());
        $altInput->css('width', '400px')->setTitle('O título é muito importante para que os mecanismos de busca (Google) encontrem suas imagens.');

        $view[] = $this->getContainer('Título', $altInput);

        $view = array(new \View\Div('selectedInfo', $view, 'selectedInfo'));

        if ($file->isImage())
        {
            $image = new \Media\Image($file->getPath());

            $imageView[] = $this->getContainer('Largura', new \View\Input('imgWidth', \View\Input::TYPE_NUMBER, Config::getDefault('mediaImageDefaultWidth', $image->getWidth())));
            $imageView[] = $this->getContainer('Altura', new \View\Input('imgHeight', \View\Input::TYPE_NUMBER, trim(Config::getDefault('mediaImageDefaultHeight', $image->getHeight()))));
            $imageView[] = new \View\Input('isImage', \View\Input::TYPE_HIDDEN, 'true');
            $view[] = new \View\Div('imageSize', $imageView, 'imageSize');
        }
        else
        {
            $view[] = new \View\Input('isImage', \View\Input::TYPE_HIDDEN, 'false');
        }

        $this->byId('selectedContainer')->html($view);
    }

    /**
     * Resize a image
     */
    public function resizeImage()
    {
        $href = explode('/', Request::get('href'));
        $href = $href[count($href) - 1];
        $image = new \Media\Image(new \Disk\Media($href));
        $image->resize(Request::get('width'), Request::get('height'));

        $newWidth = $image->getWidth();
        $newHeight = $image->getHeight();

        //user thumbnail
        $thumbFile = new \Disk\Media('thumb' . DS . $image->getBasename(FALSE) . '_' . $newWidth . '_' . $newHeight . '.' . $image->getExtension());
        $thumbFile->createFolderIfNeeded();

        $image->export($thumbFile, 80);
        \App::addJs('mediaReturnFileName = "' . $thumbFile->getUrl() . '"');
        \App::addJs('mediaReturnWidth = "' . $newWidth . '"');
        \App::addJs('mediaReturnHeight = "' . $newHeight . '"');

        //thumbinho
        $thumbinho = new \Disk\Media('thumb' . DS . $image->getBasename(FALSE) . '_thumb.' . $image->getExtension());
        $image->resize(NULL, Config::getDefault('mediaImageDefaultThumbHeight', 50));
        $image->export($thumbinho, 80);
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

        $grid = new $gridClass;
        $table = $grid->createTable();

        $element = new \View\Div(\View\View::REPLACE_SHARP . substr($gridClass, 1));
        $element->setOutputJs(TRUE);
        //remove do dom para não reaparecer
        $element->parentNode->removeChild($element);
        $element->html($table);
    }

    /**
     * Add advanced filter
     *
     * @return boolean
     */
    public function addAdvancedFilter()
    {
        \App::dontChangeUrl();
        \App::setResponse('NULL'); //for grid
        //TODO suport columns with description in name
        $originalValue = Request::get('v');
        $value = str_replace('Description', '', $originalValue);
        $grid = $this->setDefaultGrid();

        if (!$grid instanceof \Component\Grid\Grid)
        {
            return false;
        }

        $column = $grid->getColumn($value);
        $dbModel = $this->getModel();
        $mountFilter = new \Component\Grid\MountFilter($column, $dbModel);
        $filter = $mountFilter->getFilter();

        if ($filter)
        {
            $input = $filter->getInput();
            $input->append(self::getCloseFilterButton());

            //remove the filter if exists
            \App::addJs("$('#{$input->getId()}').remove();");
            //put the input inside containerFiltros
            $this->byId('containerFiltros')->append($input);
            //call js change
            \App::addJs("$('.filterCondition').change();");
            //put focus on input field
            \App::addJs("$('#{$input->getId()}').find('.filterInput').focus();");
        }
    }

    /**
     * Return the close filter icon
     *
     * @return \View\Ext\Icon
     */
    public static function getCloseFilterButton()
    {
        $icon = new \View\Ext\Icon('cancel');
        $icon->click('$(this).parent().find(\'input, select\').attr(\'disabled\',\'disabled\'); $(this).parent().hide();');
        $icon->addClass('removeFilter');

        return $icon;
    }

    /**
     * Create a search group field
     * @param \Page\Grid $grid
     * @param string $name
     * @param string $value
     * @return \View\Div|boolean
     */
    public static function createSearchGroupField($grid, $name, $value = NULL)
    {
        if (!$grid instanceof \Component\Grid\Grid)
        {
            return false;
        }

        //$grid->remove();
        $column = $grid->getColumn($name);

        $groupTypes = \Db\GroupColumn::listGroupTypes();

        foreach ($groupTypes as $groupName => $label)
        {
            $options[] = new \View\Option($groupName, $label);
        }

        $id = 'group-type[' . $name . ']';

        $label = $column instanceof \Component\Grid\Column ? $column->getLabel() : 'Todos';

        $view[] = new \View\Label(NULL, $id, 'Agrupar: ' . $label, 'filterLabel small');

        $view[] = $select = new \View\Select($id, $options, $name, 'filterCondition group small');
        $select->setValue($value);

        $view[] = self::getCloseFilterButton();

        return new \View\Div(NULL, $view, 'filterField');
    }

    /**
     * Add search group
     */
    public function addSearchGroup()
    {
        \App::dontChangeUrl();
        \App::setResponse('NULL'); //for grid
        $grid = $this->setDefaultGrid();
        $name = Request::get('selectGroups');
        $div[] = self::createSearchGroupField($grid, $name);

        $this->byId('containerFiltros')->append($div);
    }

    /**
     * Result of crop an image by image upload
     */
    public function cropImage()
    {
        \App::dontChangeUrl();

        $href = Request::get('imageHandlerHref');
        $elementId = Request::get('imageHandlerId');
        $file = new \Disk\Media($href);
        $path = $file->getPath();
        $img = new \Media\Image($path);

        $targ_w = Request::get('w');
        $targ_h = Request::get('h');

        $img->crop(Request::get('x'), Request::get('y'), $targ_w, $targ_h, Request::get('w'), Request::get('h'));
        $img->export($path);

        \App::addJs('destroyCropCanvas();');
        $img = \View\Ext\ImageUpload::getImg($href . '?_=' . rand());
        $this->byId('imgResult_' . $elementId)->html($img);
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
        return $this->byId('content');
    }

    /**
     * Add tool tip to element
     *
     * @param string $selector
     * @param string $message
     */
    public function addToolTip($selector, $message)
    {
        \App::addJs($js = "toolTip('{$selector}', '{$message}');");
    }

}
