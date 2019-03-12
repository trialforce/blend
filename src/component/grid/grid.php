<?php

namespace Component\Grid;

use DataHandle\Request;
use DataHandle\Session;

/**
 * A Simple grid, works very handy.
 */
class Grid extends \Component\Component implements \Disk\JsonAvoidPropertySerialize
{

    /**
     * Simple link to head element
     *
     * @var \View\THead
     */
    public $head;

    /**
     * Simple link to body element
     * @var \View\TBody
     */
    public $body;

    /**
     * PageName
     * @var string
     */
    protected $pageName;

    /**
     * O objeto com a tabela gerada
     *
     * @var \View\Table
     */
    protected $table;

    /**
     * Rodapé da tabela
     *
     * @var \View\View
     */
    protected $foot;

    /**
     * Título da tabela
     *
     * @var \View\Vieww
     */
    protected $title;

    /**
     * Paginador
     *
     * @var \View\View
     */
    protected $paginator;

    /**
     * Cache identificator column, used for cache
     *
     * @var \Component\Grid\Column
     */
    protected $identificatorColumn;

    /**
     * Grid DataSource
     *
     * @var \DataSource\DataSource;
     */
    protected $dataSource;

    /**
     * Construct a grid
     *
     * @param string $id
     * @param \DataSource\DataSource $dataSource
     */
    public function __construct($id = NULL, $dataSource = NULL)
    {
        parent::__construct($id);
        $this->setDataSource($dataSource);
    }

    /**
     * Retorna o datasource selecionado.
     * Já com as colunas definidas
     *
     * @return \DataSource\Model
     */
    public function getDataSource()
    {
        return $this->dataSource;
    }

    /**
     * Define the DataSource
     * @param type $dataSource
     */
    public function setDataSource($dataSource)
    {
        $this->dataSource = $dataSource;
    }

    public function setPageName($pageName)
    {
        $this->pageName = $pageName;
        return $this;
    }

    public function getPageName()
    {
        if ($this->pageName)
        {
            return $this->pageName;
        }

        return \View\View::getDom()->getPageUrl();
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Retorna as colunas da grid
     *
     * @return array
     */
    public function getColumns()
    {
        return $this->dataSource->getColumns();
    }

    /**
     * Retorna as colunas da grid que são visíveis
     * @return array
     */
    public function getRenderColumns()
    {
        $renderColumns = array();
        $columns = $this->getColumns();

        if (is_array($columns))
        {
            foreach ($columns as $column)
            {
                if (!$column)
                {
                    continue;
                }

                if ($column->getRender())
                {
                    $renderColumns[] = $column;
                }
            }
        }

        return $renderColumns;
    }

    public function setColumns($columns)
    {
        if (is_array($columns))
        {
            foreach ($columns as $column)
            {
                $column->setGrid($this);
            }
        }

        $this->dataSource->setColumns($columns);
        return $this;
    }

    /**
     * Remove an unnecessary columns
     *
     * @param string $name name of colum to remove
     */
    public function removeColumn($name)
    {
        return $this->dataSource->removeColumn($name);
    }

    /**
     * Return some column
     *
     * @param string $name
     * @return \Component\Grid\Column
     */
    public function getColumn($name)
    {
        $columns = $this->getColumns();

        if (is_array($columns))
        {
            foreach ($columns as $column)
            {
                $column->setGrid($this);

                if ($column->getName() == $name)
                {
                    return $column;
                }
            }
        }
    }

    /**
     * Get indentificator column of grid.
     *
     * Make cache to avoid overhead
     *
     * @return \Component\Grid\Column
     */
    public function getIdentificatorColumn()
    {
        if (isset($this->identificatorColumn))
        {
            return $this->identificatorColumn;
        }

        $columns = $this->getColumns();

        foreach ($columns as $column)
        {
            if (!$column)
            {
                continue;
            }

            $column->setGrid($this);

            if ($column->getIdentificator())
            {
                $this->identificatorColumn = $column;
                return $this->identificatorColumn;
            }
        }
    }

    /**
     * Simple function that make the grid a simple table
     *
     * @return \Component\Grid
     */
    public function makeSimple()
    {
        $columns = $this->getColumns();

        foreach ($columns as $column)
        {
            $column->setOrder(FALSE);
        }

        $this->setPaginator(' ');

        return $this;
    }

    /**
     * Make the creation of the grid
     *
     * @return \Component\Grid
     */
    public function onCreate()
    {
        //avoid double creation
        if ($this->isCreated())
        {
            return $this->getContent();
        }

        $this->setContent($this->createTable());

        return $this->getContent();
    }

    /**
     * Create table
     * @return
     */
    protected function createTable()
    {
        $view = array();

        if ($this->getTitle())
        {
            $captionName = strtolower(str_replace('\\', '-', $this->getId()) . '-caption');
            $view[] = new \View\Caption($captionName, $this->getTitle());
        }

        $view[] = $this->mountColGroup();

        $view[] = $this->head = new \View\THead(NULL, $this->mountHead());
        $view[] = $this->body = new \View\TBody(NULL, $this->mountData());
        $view[] = $this->foot = $this->mountFoot();

        $this->table = new \View\Table($this->getId() . 'Table', $view);

        $div = new \View\Div($this->getId(), $this->table, 'grid');

        $this->makeAggregation();

        return $div;
    }

    /**
     * Create grid footer
     *
     * @return \View\TFoot
     */
    public function mountFoot()
    {
        $paginator = $this->getPaginator();

        //add crappy support for no paginator
        if ($paginator && !(is_string($paginator) && trim($paginator) == ''))
        {
            if (is_object($paginator) && method_exists($paginator, 'onCreate'))
            {
                $paginator->onCreate();
            }

            $td = new \View\Td(NULL, $paginator, 'tdPaginator');
            $td->setColspan(count($this->getRenderColumns()));
            $tr = new \View\Tr(NULL, $td);

            return new \View\TFoot(NULL, $tr);
        }
    }

    /**
     * Mount col group
     * @return \View\ColGroup
     */
    public function mountColGroup()
    {
        $orderBy = $this->getDataSource()->getOrderBy();
        $columns = $this->getColumns();
        $cols = null;

        if (is_array($columns))
        {
            foreach ($columns as $column)
            {
                if (!$column)
                {
                    continue;
                }

                $column instanceof \Component\Grid\Column;

                $column->setGrid($this);

                //jump column that not render
                if (!$column->getRender())
                {
                    continue;
                }

                $cols[] = $col = new \View\Col('col-' . $column->getName(), NULL, null);
                $align = str_replace('align', '', $column->getAlign());
                $col->setAttribute('align', lcfirst($align));
                $col->setData('type', $column->getType());
                $col->setData('name', $column->getName());
                $col->setData('label', $column->getLabel());

                if ($column->getWidth())
                {
                    $col->css('width', $column->getWidth());
                }
            }
        }

        $colGroup = new \View\ColGroup(null, $cols);

        return $colGroup;
    }

    /**
     * Make Aggregation
     */
    public function makeAggregation()
    {
        $dataSource = $this->getDataSource();
        $aggregators = $dataSource->getAggregator();

        if (count($aggregators) == 0)
        {
            return;
        }

        $columns = $this->getRenderColumns();
        $td = null;

        foreach ($columns as $column)
        {
            $value = '';

            if (isset($aggregators[$column->getName()]) && $aggregators[$column->getName()] instanceof \DataSource\Aggregator)
            {
                $aggr = $aggregators[$column->getName()];

                $value = $dataSource->executeAggregator($aggr);
            }

            $class = 'aggr ' . $column->getAlign();

            if ($column->getIdentificator())
            {
                $class .= ' identificator';
            }

            $td[] = new \View\Td('aggr' . $column->getName(), $value, $class);
        }

        $this->body->append(new \View\Tr('aggreLine', $td, 'aggr'));
    }

    /**
     * Retorna o paginador
     *
     * @return \Component\Grid\Paginator
     */
    public function getPaginator()
    {
        //caso não tenha instancia
        if (!$this->paginator)
        {
            $this->paginator = new \Component\Grid\Paginator('paginator', $this);
        }

        return $this->paginator;
    }

    /**
     * Define paginator
     *
     * @param \View\View $paginator
     * @return \Component\Grid
     */
    public function setPaginator($paginator)
    {
        $this->paginator = $paginator;

        return $this;
    }

    /**
     * Mount the head of table
     *
     * @return \View\Tr
     */
    protected function mountHead()
    {
        $columns = $this->getRenderColumns();
        $th = array();

        $views[] = $viewTr = new \View\Tr( );

        if (is_array($columns))
        {
            foreach ($columns as $column)
            {
                $column->setGrid($this);
                $th[] = $viewTh = new \View\Th($column->getName() . 'Th', $column->getLabel(), $column->getAlign());
                $viewTh->html($column->getHeadContent($viewTr, $viewTh));
            }
        }

        $viewTr->html($th);


        return $views;
    }

    /**
     * Get a link to some event on grid
     * Used in \Component\Grid\Column
     *
     * @param string $event
     * @param string $value
     * @return string
     */
    public function getLink($event = NULL, $value = NULL, $params = NULL, $putUrl = TRUE)
    {
        $queryString = array();

        if ($putUrl)
        {
            parse_str(\DataHandle\Server::getInstance()->get('QUERY_STRING'), $queryString);
            unset($queryString['p']);
            unset($queryString['selectFilters']);
            unset($queryString['selectGroups']);
            unset($queryString['_']);
            unset($queryString['e']);
            unset($queryString['v']);
            unset($queryString['formChanged']);
            unset($queryString['total_notificacoes']);
            unset($queryString['paginationLimit']);

            if (is_array($params))
            {
                $queryString = array_merge($queryString, $params);
            }
        }
        else if (is_array($params))
        {
            $queryString = $params;
        }

        $url = http_build_query($queryString);
        $url = strlen($url) > 0 ? '?' . $url : '';

        return str_replace('//', '/', str_replace('//', '/', $this->getPageName() . '/' . $event . '/' . $value . $url)); // . '/?stateId=' . $this->getId();
    }

    /**
     * Mount the data of table, according each column definition
     *
     * @return \View\Tr array of
     */
    protected function mountData()
    {
        $dataSource = $this->getDataSource();
        $data = $dataSource->getData();
        $columns = $this->getRenderColumns();
        $tr = array();

        if (isIterable($data))
        {
            foreach ($data as $index => $item)
            {
                $tr[] = $this->createTr($columns, $index, $item);
            }
        }

        return $tr;
    }

    protected function createTr($columns, $index, $item)
    {
        $tr = new \View\Tr(NULL, NULL, $index % 2 ? 'alt' : 'normal');

        //parse item data to grid
        $itemParsed = $this->parseItemData($item);
        $td = array();

        $td[] = $this->createTdMobile($columns, $index, $itemParsed, $tr);

        foreach ($columns as $column)
        {
            $td[] = $this->createTd($column, $index, $itemParsed, $tr);
        }

        return $tr->html($td);
    }

    protected function createTdMobile($columns, $index, $item, $tr)
    {
        $td = new \View\Td(NULL, NULL, 'hide-in-desktop');

        return $td->html($this->createMobileContent($columns, $index, $item, $tr));
    }

    protected function createMobileContent($columns, $index, $item, $tr)
    {
        $tr = array();

        foreach ($columns as $column)
        {
            $column instanceof \Component\Grid\Column;
            $tr[] = $myTr = new \View\Tr();
            $td = array();

            if (!$column->getIdentificator())
            {
                $td[0] = new \View\Td(null, new \View\B(null, $column->getLabel() . ':'), 'td-left');
            }

            $td[1] = new \View\Td();

            //workaround for editable columns
            if ($column->getEdit() == true)
            {
                $value = \Component\Grid\Column::getColumnValue($column, $item);
            }
            else
            {
                $value = $column->getValue($item, $index, $myTr, $td[1]);
            }

            if ($value instanceof \Type\Generic)
            {
                $value = $value->toHuman();
            }

            $td[1]->html($value);
            $myTr->html($td);
        }

        return new \View\Table(null, $tr, 'table-inner-mobile');
    }

    /**
     * Create one \View\Td used in table
     *
     * @param \Component\Grid\Column $column
     * @param mixed $item
     * @return \View\Td
     */
    protected function createTd(\Component\Grid\Column $column, $index, $item, $tr)
    {
        $td = new \View\Td(NULL, NULL, $column->getAlign() . ' hide-in-mobile');
        $value = $column->getValue($item, $index, $tr, $td);

        if ($value instanceof \Type\Generic)
        {
            $value = $value->toHuman();
        }

        return $td->html($value);
    }

    /**
     * Função que pode ser sobreescrita para tratar a visualização do dados na grid.
     *
     * Chamada para cada um dos itens.
     *
     * @param \stdClass $item
     * @return \stdClass
     */
    public function parseItemData($item)
    {
        return $item;
    }

    /**
     * Gera um arquivo CSV baseado no momento atual da grid
     *
     * @return \Disk\File
     */
    public function exportFile($type = 'CSV', $columns = NULL, $pageSize = NULL)
    {
        $dataSource = $this->getDataSource();
        $class = '\DataSource\Export\\' . $type;

        if (!class_exists($class))
        {
            throw new \Exception('Impossível encontrar classe para geração de ' . $type);
        }

        $file = $class::create($dataSource, $this->getId(), $columns, $pageSize);

        return $file;
    }

    /**
     * Add search filters to dataSource
     *
     * @param \DataSource\DataSource $dataSource
     * @return \DataSource\DataSource $dataSource
     */
    public static function addFiltersToDataSource(\DataSource\DataSource $dataSource)
    {
        self::addPaginationToDataSource($dataSource);

        //this need to be optimized, this is in wrong place, but for compatibily porpouses is here
        $page = \View\View::getDom();

        if (method_exists($page, 'getModel'))
        {
            $grid = $page->getGrid();
            $extraFilters = null;
            //FIXME optimize 10% if get only what is is post
            if (method_exists($grid, 'getSearchField'))
            {
                $searchField = $grid->getSearchField();
                $extraFilters = $searchField->getExtraFilters();
            }

            $filters = \Component\Grid\MountFilter::getFilters($dataSource->getColumns(), $page->getModel(), $extraFilters);

            if (is_array($filters))
            {
                foreach ($filters as $filter)
                {
                    $dbCond = $filter->getDbCond();

                    if ($dbCond)
                    {
                        $dataSource->addExtraFilter($dbCond);
                    }
                }
            }
        }

        return $dataSource->setSmartFilter(Request::get('q'));
    }

    /**
     * Add search filters to dataSource
     *
     * @param \DataSource\DataSource $dataSource
     * @return \DataSource\DataSource $dataSource
     */
    public static function addPaginationToDataSource(\DataSource\DataSource $dataSource)
    {
        $dataSource->setPaginationLimit(\Component\Grid\Paginator::getCurrentPaginationLimitValue());

        if (Request::get('orderBy'))
        {
            $dataSource->setOrderBy(Request::get('orderBy'));
        }

        if (Request::get('orderWay'))
        {
            $dataSource->setOrderWay(Request::get('orderWay'));
        }

        if (Request::get('page'))
        {
            $dataSource->setPage(Request::get('page'));
        }
        else
        {
            if (is_null($dataSource->getLimit()))
            {
                $dataSource->setPage(0);
            }
        }
    }

    /**
     * Return the file from report columns
     *
     * @return \Disk\File
     */
    public function getReportColumnsFile()
    {
        $idUser = Session::get('user') ? Session::get('user') . '/' : '';
        $fileReportColumns = \Disk\File::getFromStorage($idUser . 'report-columns-' . $this->getPageName() . '.json');

        return $fileReportColumns;
    }

    /**
     * Grid export data, used in component grid
     * @return type
     */
    public function gridExportData()
    {
        \App::dontChangeUrl();
        $grid = $this;
        $columns = $grid->getColumns();
        $checks = null;

        //get selected columns from file
        $fileReportColumns = $this->getReportColumnsFile();
        $fileReportColumns->load();
        $selectedColumns = json_decode($fileReportColumns->getContent());

        if (is_array($columns))
        {
            foreach ($columns as $column)
            {
                if ($column->getExport())
                {
                    $columnSelected = $column->getRender();

                    if (is_array($selectedColumns))
                    {
                        $columnSelected = in_array($column->getName(), $selectedColumns);
                    }

                    $idName = 'reportColumns[' . $column->getName() . ']';
                    $line = array();
                    $line[] = new \View\Ext\CheckboxDb($idName, $idName, $columnSelected);
                    $line[] = new \View\Label(NULL, $idName, $column->getLabel());
                    $checks[] = new \View\Div(null, $line);
                }
            }
        }

        $selectColumns = new \View\Div('exportColumns', $checks, 'exportColumns ');

        $left[] = new \View\Div(NULL, 'Colunas');
        $left[] = $selectColumns;

        $right[] = new \View\Div( );

        $formats['csv'] = 'CSV (Excel)';
        $formats['html'] = 'HTML (Tela)';
        $formats['pdf'] = 'PDF (Impressão)';

        $formatos[] = new \View\Label(NULL, 'format', 'Formato', 'field-label');
        $formatos[] = $abc = new \View\Select('format', $formats, 'csv');
        $abc->change("if ( $(this).val() == 'pdf' ) { $('#reportPageSize_contain').show(); } else { $('#reportPageSize_contain').hide(); }");

        $right[] = new \View\Div(NULL, $formatos, 'field-contain');

        $pageSizes['A4'] = 'A4 (Retrato)';
        $pageSizes['A4-L'] = 'A4 (Paisagem)';

        $sizes[] = new \View\Label(NULL, 'reportPageSize', 'Página', 'field-label');
        $sizes[] = $pageSize = new \View\Select('reportPageSize', $pageSizes, 'csv');
        $pageSize->selectFirst();
        $right[] = $pageSizeContain = new \View\Div('reportPageSize_contain', $sizes, 'field-contain');
        $pageSizeContain->hide();

        $view[] = new \View\Div('left', $left, 'fl');
        $view[] = new \View\Div('right', $right, 'fr alignLeft');

        $url = $this->getLink('exportGridFile');

        $buttons[] = new \View\Ext\LinkButton('exportGridFile', 'download', 'Gerar arquivo', $url, 'primary');
        $buttons[] = new \View\Ext\Button('cancel', 'cancel', 'Cancelar', \View\Blend\Popup::getJs('destroy'));
        $popup = new \View\Blend\Popup('gridExportData', 'Criação de relatórios / exportação de dados ', $view, $buttons);
        $popup->setIcon('download');

        return $popup->show();
    }

    /**
     * Export file from grid, used in component grid
     *
     * @throws \Exception
     */
    public function exportGridFile()
    {
        \App::dontChangeUrl();
        $dataSource = $this->getDataSource();
        $this->addFiltersToDataSource($dataSource);
        $type = str_replace("/", '', Request::get('format'));
        $reportColumns = array_keys(Request::get('reportColumns'));

        //save selected filter in file, to restore
        $fileReportColumns = $this->getReportColumnsFile();
        $fileReportColumns->save(json_encode($reportColumns));

        $pageSize = Request::get('reportPageSize');

        return $this->exportFile($type, $reportColumns, $pageSize)->outputToBrowser();
    }

    /**
     * Create dataSource used in component grids
     *
     * @param type $reference
     * @return \DataSource\Model
     */
    public function createDataSource($reference)
    {
        $ds = new \DataSource\Model($reference);
        //compatibility with search grid
        $this->model = $reference;
        $this->addFiltersToDataSource($ds);

        return $ds;
    }

    /**
     * Listar, used in component grid
     */
    public function listar()
    {
        \App::dontChangeUrl();
        $id = $this->getId();
        $dom = \View\View::getDom();
        $div = $dom->byId($id);

        $table = $this->createTable();
        $div->html($table);
    }

    public function listAvoidPropertySerialize()
    {
        $avoid = parent::listAvoidPropertySerialize();
        $avoid[] = 'head';
        $avoid[] = 'body';
        $avoid[] = 'pageName';
        $avoid[] = 'columns';
        $avoid[] = 'filters';
        $avoid[] = 'foot';
        $avoid[] = 'table';
        $avoid[] = 'identificatorColumn';
        $avoid[] = 'callInterfaceFunctions';

        return $avoid;
    }

}
