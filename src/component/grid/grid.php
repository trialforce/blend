<?php

namespace Component\Grid;

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

    public function mountColGroup()
    {
        return null;
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
        $dataSource->setColumns($this->getColumns());
        $data = $dataSource->getData();
        $columns = $this->getRenderColumns();
        $tr = array();

        if (is_array($data))
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

        if (method_exists($item, 'fillExtraData'))
        {
            $item = $item->fillExtraData();
        }

        //parse item data to grid
        $item2 = $this->parseItemData($item);
        $td = array();

        foreach ($columns as $column)
        {
            $td[] = $this->createTd($column, $index, $item2, $tr);
        }

        return $tr->html($td);
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
        $td = new \View\Td(NULL, NULL, $column->getAlign());
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
