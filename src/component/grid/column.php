<?php

namespace Component\Grid;

/**
 * Coluna da grid
 */
class Column
{

    /**
     * Align left
     */
    const ALIGN_LEFT = 'alignLeft';

    /**
     * Align right
     */
    const ALIGN_RIGHT = 'alignRight';

    /**
     * Align center
     */
    const ALIGN_CENTER = 'alignCenter';

    /**
     * Align justify
     */
    const ALIGN_JUSTIFY = 'alignJustify';

    /**
     * Align colapse
     */
    const ALIGN_COLAPSE = 'alignColapse';

    /**
     * ALign icon
     */
    const ALIGN_ICON = 'alignIcon';

    /**
     * Name
     * @var string
     */
    protected $name;

    /**
     * Sql for the column
     *
     * @var string
     */
    protected $sql;

    /**
     * Label
     * @var string
     */
    protected $label;

    /**
     * Align
     *
     * @var string
     */
    protected $align = self::ALIGN_LEFT;

    /**
     * O tipo de dados da coluna
     *
     * @var string
     */
    protected $type;

    /**
     * If column is identificator ( id )
     *
     * @var boolean
     */
    protected $identificator = FALSE;

    /**
     * If is to render this column in default render
     *
     * @var boolean
     */
    protected $render = TRUE;

    /**
     * If is to render this column in detail
     *
     * @var boolean
     */
    protected $renderInDetail = TRUE;

    /**
     * Determina se é ou não para exportar essa coluna
     *
     * @var boolean
     */
    protected $export = TRUE;

    /**
     * Determina se a coluna é filtrável ou não.
     * Ou seja, se irá gerar um filtro automático.
     * @todo rename to filterType
     *
     * @var boolean
     */
    protected $filter = TRUE;

    /**
     * Define if this column go to Smart filter column list
     *
     * @var boolean
     */
    protected $smartFilter = TRUE;

    /**
     * Define the label used in filter
     * @var String
     */
    protected $filterLabel;

    /**
     * Determina se a coluna é ordenável
     *
     * @var boolean
     */
    protected $order = TRUE;

    /**
     * Define if the grid columns is editable
     *
     * @var boolean
     */
    protected $edit = FALSE;

    /**
     * Width
     * @var int
     */
    protected $width;

    /**
     * Define if collumn has fixed height
     *
     * @var boolean
     */
    protected $fixedHeight = FALSE;

    /**
     * Grid
     *
     * @var \Component\Grid\Simple
     */
    protected $grid;

    /**
     * A type to apply a format to value
     *
     * @var \Type\Generic
     */
    protected $formatter;

    /**
     * Construct the column
     *
     * @param string $name name
     * @param string $label label
     * @param string $align align
     */
    public function __construct($name = NULL, $label = NULL, $align = Column::ALIGN_LEFT, $dataType = \Db\Column\Column::TYPE_VARCHAR)
    {
        $this->setName($name);
        $this->setLabel($label);
        $this->setAlign($align);
        $this->setType($dataType);
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Return a safe name consider accent, spaces or special chars
     *
     * @return string
     */
    public function getSafeName()
    {
        $text = \Type\Text::get($this->name);

        $text->toASCII();
        $text->toLower();
        $text->replace(array(' ', '.', ':', '/', '\\', '\'', '"', '[', ']', '='), '-');
        $text->replace(array('?', '!', '(', ')', ',', '+', '%', '|'), '');

        return $text->toHuman();
    }

    /**
     * Define name
     *
     * @param string $name
     * @return \Component\Grid\Column
     */
    public function setName($name)
    {
        $this->name = $name;
        $this->sql = $name;
        return $this;
    }

    function getSql()
    {
        return $this->sql;
    }

    function setSql($sql)
    {
        $this->sql = $sql;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setType($dataType)
    {
        $this->type = $dataType;

        return $this;
    }

    /**
     * Get label
     *
     * @return string
     */
    public function getLabel()
    {
        return ucfirst($this->label);
    }

    /**
     * Define label
     *
     * @param string $label
     * @return \Component\Grid\Column
     */
    public function setLabel($label)
    {
        $this->label = $label;
        return $this;
    }

    /**
     * Get align
     *
     * @return string
     */
    public function getAlign()
    {
        return $this->align;
    }

    /**
     * Define align
     *
     * @param string $align
     * @return \Component\Grid\Column
     */
    public function setAlign($align)
    {
        $this->align = $align;
        return $this;
    }

    /**
     * Get indentificator
     *
     * @return string
     */
    public function getIdentificator()
    {
        return $this->identificator;
    }

    /**
     * Set identificator
     *
     * @param string $identificator
     * @return \Component\Grid\Column
     */
    public function setIdentificator($identificator)
    {
        $this->identificator = $identificator;
        return $this;
    }

    public function getEdit()
    {
        return $this->edit;
    }

    public function setEdit($edit)
    {
        $this->edit = $edit;
        return $this;
    }

    /**
     * Return the grid element.
     *
     * @return \Component\Grid\Grid
     */
    public function getGrid()
    {
        return $this->grid;
    }

    /**
     * Define the grid element.
     * @param type $grid
     *
     * @return \Component\Grid\Simple
     */
    public function setGrid($grid)
    {
        $this->grid = $grid;
        return $this;
    }

    /**
     * Get render
     *
     * @return boolean
     */
    public function getRender()
    {
        return $this->render;
    }

    /**
     * Define if is to render column
     *
     * @param boolean $render
     * @return \Component\Grid\Column
     */
    public function setRender($render = FALSE)
    {
        $this->render = $render;
        return $this;
    }

    /**
     * Return if the column is to be rendered in detail
     * @return boolean
     */
    public function getRenderInDetail()
    {
        return $this->renderInDetail;
    }

    /**
     * Define if the column is to be rendered in detail
     *
     * @param v $renderInDetail
     * @return $this
     */
    public function setRenderInDetail($renderInDetail)
    {
        $this->renderInDetail = $renderInDetail;
        return $this;
    }

    public function getExport()
    {
        return $this->export;
    }

    public function setExport($export)
    {
        $this->export = $export;
        return $this;
    }

    public function getFilter()
    {
        return $this->filter;
    }

    public function getSmartFilter()
    {
        return $this->smartFilter;
    }

    public function setSmartFilter($smartFilter)
    {
        $this->smartFilter = $smartFilter;
        return $this;
    }

    public function getFilterType()
    {
        return $this->filter;
    }

    public function setFilter($filter)
    {
        $this->filter = $filter;
        return $this;
    }

    public function getFilterLabel()
    {
        return $this->filterLabel ? $this->filterLabel : $this->label;
    }

    public function setFilterLabel($filterLabel)
    {
        $this->filterLabel = $filterLabel;
        return $this;
    }

    public function getOrder()
    {
        return $this->order;
    }

    public function setOrder($order)
    {
        $this->order = $order;
        return $this;
    }

    public function getFixedHeight()
    {
        return $this->fixedHeight;
    }

    public function setFixedHeight($fixedHeight)
    {
        $this->fixedHeight = $fixedHeight;
        return $this;
    }

    public function getWidth()
    {
        return $this->width;
    }

    public function setWidth($width)
    {
        $this->width = $width;

        return $this;
    }

    /**
     * Return the column value formatter
     *
     * @return \Type\Generic
     */
    public function getFormatter()
    {
        return $this->formatter;
    }

    /**
     * Define the column type formatter
     *
     * @param \Type\Generic $formatter
     * @return $this
     */
    public function setFormatter(\Type\Generic $formatter)
    {
        $this->formatter = $formatter;
        return $this;
    }

    /**
     * Parse and format the value.
     *
     * @param object $item
     *
     * @return mixed
     */
    public function getValue($item, $line = NULL, \View\View $tr = NULL, \View\View $td = NULL)
    {
        $value = \DataSource\Grab::getUserValue($this, $item, $line);
        $this->makeEditable($item, $line, $tr, $td);

        if ($this->getFixedHeight())
        {
            $view = new \View\Div(NULL, $value, 'fixedHeight');
            $view->setTitle(strip_tags($value));
            return $view;
        }
        else
        {
            return $value;
        }
    }

    public function makeEditable($item, $line = NULL, \View\View $tr = NULL, \View\View $td = NULL)
    {
        //not used in this case
        $line = NULL;
        $tr = NULL;

        if ($this->getEdit() && $td)
        {
            $dom = \View\View::getDom();
            $pageUrl = $dom->getPageUrl();
            $columName = $this->getName();
            $pkValue = \DataSource\Grab::getUserValue($this->getGrid()->getIdentificatorColumn(), $item);
            $td->setId('gridColumn-' . $this->getName() . '-' . $pkValue);
            $td->click("p('{$pageUrl}/gridEdit/$pkValue/?columnName={$columName}');");
        }
    }

    /**
     * Create the head content
     */
    public function getHeadContent(\View\View $tr, \View\View $th)
    {
        //widhout order, uses only label
        if (!$this->getOrder())
        {
            return $this->getLabel();
        }

        $safeName = $this->getSafeName();
        $grid = $this->getGrid();
        $dataSource = $grid->getDataSource();
        $order = $dataSource->getOrderByParsedForColumn($safeName);
        $newOrderWay = 'asc';
        $class = 'orderBy ';

        if ($order->orderWay == 'asc')
        {
            $class .= 'fa fa-angle-up';
            $newOrderWay = 'desc';
        }
        else if ($order->orderWay == 'desc')
        {
            $class .= 'fa fa-angle-down';
            $newOrderWay = '';
        }

        $orderFromPost = urldecode(\DataHandle\Request::get('orderBy'));
        $orders = $dataSource->getOrderByParsed($orderFromPost);
        $orders[$safeName] = new \stdClass();
        $orders[$safeName]->orderBy = $safeName;
        $orders[$safeName]->orderWay = $newOrderWay;

        $orderSql = [];

        foreach ($orders as $order)
        {
            $orderSql[] = $order->orderBy . ' ' . $order->orderWay;
        }

        $orderTxt = implode(', ', $orderSql);
        $param['orderBy'] = urlencode($orderTxt);

        //normal link
        $url = $this->getGrid()->getLink('listar', '', $param);
        $link = new \View\A('order' . ucfirst($this->getSafeName()), $this->getLabel() . ' ', $url);

        if ($order->orderBy)
        {
            byId('col-' . $this->getName())->addClass('order-by');
            $i = new \View\I(null, null, $class);
            $link->appendChild($i);
        }

        return $link;
    }

    /**
     * Used by some column like link and image
     *
     * @param string $string
     * @param \Db\Model $item
     * @return string
     */
    public function replaceDataInString($string, $item)
    {
        $identificator = $this->getGrid()->getIdentificatorColumn();
        $idValue = \DataSource\Grab::getDbValue($identificator, $item);

        //make pk more simple
        $string = str_replace(':?', $idValue, $string);

        if (is_object($item))
        {
            $itemArray = (array) $item;
        }

        foreach ($itemArray as $property => $val)
        {
            $property = trim(str_replace('*', '', $property));
            $val = \DataSource\Grab::getDbValue($property, $item);
            $string = str_replace(':' . $property . '?', $val, $string);
        }

        return $string;
    }

    public function __toString()
    {
        return $this->getName() . '';
    }

    /**
     * Return the name of the column.
     * But control '.' as AS
     *
     * @deprecated since version 2019-01-18 use \Db\Column\Column::getRealColumnName
     *
     * @return string
     */
    public function getSplitName()
    {
        return \Db\Column\Column::getRealColumnName($this->getName());
    }

    /**
     * Return the value of the column, the simple value, without magic
     *
     * @deprecated since version 2019-10-06 use \DataSource\Grab::getDbValue
     *
     * @param string $column
     * @param \Db\Model $item
     *
     */
    public static function getColumnSimpleValue($column, $item)
    {
        return \DataSource\Grab::getDbValue($column, $item);
    }

    /**
     * Return the value of the object for the columns,
     * uses magic to get user value
     *
     * @deprecated since version 2019-10-06 use \DataSource\Grab::getUserValue
     *
     * @param \Component\Grid\Column $column
     * @param mixed $item
     *
     * @return string
     */
    public static function getColumnValue($column, $item)
    {
        return \DataSource\Grab::getUserValue($column, $item);
    }

}
