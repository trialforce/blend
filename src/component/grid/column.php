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
     * If is to render columns
     *
     * @var boolean
     */
    protected $render = TRUE;

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
    public function __construct($name = NULL, $label = NULL, $align = Column::ALIGN_LEFT, $dataType = \Db\Column::TYPE_VARCHAR)
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
     * Return a safe name with accent, spaces or special chars
     *
     * @return string
     */
    public function getSafeName()
    {
        return \Type\Text::get($this->name)->toFile('-')->toHuman();
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
     * @return \Component\Grid
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

    public function getFilterType()
    {
        return $this->filter ? $this->filter : \Db\Cond::TYPE_NORMAL;
    }

    public function setFilter($filter)
    {
        $this->filter = $filter;
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
     * Return the name of the column.
     * But control '.' as AS
     *
     * @deprecated since version 18/01/2019 use \Db\Column::getRealColumnName
     *
     * @return string
     */
    public function getSplitName()
    {
        return \Db\Column::getRealColumnName($this->getName());
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
        $value = self::getColumnValue($this, $item, $line);
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
            $columName = $this->getName();
            $pkValue = self::getColumnValue($this->getGrid()->getIdentificatorColumn(), $item);
            $td->setId('gridColumn-' . $this->getName() . '-' . $pkValue);
            $td->click("e('gridEdit/$pkValue/?columnName={$columName}');");
        }
    }

    /**
     * Create the head content
     */
    public function getHeadContent(\View\View $tr, \View\View $th)
    {
        //sem ordenação, só rola a label
        if (!$this->getOrder())
        {
            return $this->getLabel();
        }

        $orderBy = $this->getGrid()->getDataSource()->getOrderBy();
        $orderWay = $this->getGrid()->getDataSource()->getOrderWay();

        $newOrderWay = $orderWay == 'asc' ? 'desc' : 'asc';

        $param['orderBy'] = $this->getSafeName();
        $param['orderWay'] = $newOrderWay;

        //normal link
        $url = $this->getGrid()->getLink('listar', '', $param);
        $link = new \View\A('order' . ucfirst($this->getSafeName()), $this->getLabel() . ' ', $url);

        if ($orderBy === $this->getSql())
        {
            byId('col-' . $this->getName())->addClass('order-by');
            $class = 'orderBy ';
            $class .= $orderWay == 'asc' ? 'fa fa-sort-down' : 'fa fa-sort-up';
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
        $idValue = \Component\Grid\Column::getColumnSimpleValue($identificator, $item);

        //make pk more simple
        $string = str_replace(':?', $idValue, $string);

        if (is_object($item))
        {
            $itemArray = (array) $item;
        }

        foreach ($itemArray as $property => $val)
        {
            $property = str_replace(' * ', '', $property);

            $val = \Component\Grid\Column::getColumnSimpleValue($property, $item);

            if ($val instanceof \Disk\File)
            {
                $val = $val->getUrl();
            }

            $string = str_replace(':' . $property . '?', $val, $string);
        }

        return $string;
    }

    public function __toString()
    {
        return $this->getName() . '';
    }

    /**
     * Return the value of the column, the simple value, without magic
     *
     * @param string $column
     * @param \Db\Model $item
     * @param string $line
     */
    public static function getColumnSimpleValue($column, $item, $line = NULL)
    {
        $line = null;

        $columnName = '';

        if (is_string($column))
        {
            $columnName = $column;
        }
        else if (method_exists($column, 'getSplitName'))
        {
            $columnName = $column->getSplitName();
        }

        if ($item instanceof \Db\Model)
        {
            $value = $item->getValue($columnName);
        }
        else if (is_object($item))
        {
            $grid = \View\View::getDom()->getGrid();
            $ds = null;

            if ($grid)
            {
                $ds = $grid->getDataSource();

                if ($ds instanceof \DataSource\ModelGroup)
                {
                    $columns = $ds->getOriginalColumns();

                    if (isset($columns[$columnName]))
                    {
                        $dbColumn = $columns[$columnName];
                        $columnName = $dbColumn->getProperty() ? $dbColumn->getProperty() : $columnName;
                    }
                }
            }

            $methodName = 'get' . $columnName;

            if (method_exists($item, $methodName))
            {
                $value = $item->$methodName();
            }
            else if (isset($item->{$columnName}))
            {
                $value = $item->{$columnName};
            }
        }
        else if (is_array($item))
        {
            if (isset($item[$columnName]))
            {
                $value = $item[$columnName];
            }
        }

        //add suppor for file, need better automated method for do this
        if ($value instanceof \Disk\File)
        {
            $value = $value->getUrl();
        }

        return $value;
    }

    /**
     * Return the value of the object for the columns,
     * uses magic to get user value
     *
     * @param \Component\Grid\Column $column
     * @param mixed $item
     * @param int $line
     *
     * @return string
     */
    public static function getColumnValue($column, $item)
    {
        if (!$column)
        {
            return null;
        }

        if (is_string($column))
        {
            $columnName = $column;
        }
        else if (method_exists($column, 'getSplitName'))
        {
            $columnName = $column->getSplitName();
        }

        //if is array, convert it do object, to locate tigs
        if (is_array($item))
        {
            $item = (object) $item;
        }

        $value = NULL;

        if ($item instanceof \Db\Model)
        {
            $value = $item->getValue($columnName);
            $dbColumn = $item->getColumn($columnName);

            if ($dbColumn)
            {
                $cValues = $dbColumn->getConstantValues();

                if (isIterable($cValues) || $cValues instanceof \Db\ConstantValues)
                {
                    if ($cValues instanceof \Db\ConstantValues)
                    {
                        $cValues = $cValues->getArray();
                    }

                    $constantValues = $cValues;
                    $valueConstant = $value;

                    if (is_object($value))
                    {
                        if ($value instanceof \Type\Generic)
                        {
                            $value = $value->toDb();
                        }
                    }

                    if (isset($constantValues[$value]))
                    {
                        $valueConstant = $constantValues[$value];
                    }

                    //if has valueConstant use it
                    $value = $valueConstant;

                    //add supports for simple object inside collection
                    if (is_object($value))
                    {
                        //if is a simple object, it presumes second property
                        //is the description, and firs is id
                        $array = array_values((array) $value);

                        if (isset($array[1]))
                        {
                            $value = $array[0] . '-' . $array[1];
                        }
                    }
                }
                else if ($dbColumn->getReferenceDescription())
                {
                    $columnDescriptionName = $columnName . 'Description';
                    $value = $item->getValue($columnDescriptionName);
                }
            }
        }
        else if (is_object($item))
        {
            $grid = null;
            $dom = \View\View::getDom();

            if (method_exists($dom, 'getGrid'))
            {
                $grid = $dom->getGrid();
            }

            $ds = $grid ? $grid->getDataSource() : null;
            $methodName = 'get' . $columnName;

            if (method_exists($item, $methodName))
            {
                $value = $item->$methodName();
            }
            else if (isset($item->{$columnName}))
            {
                $value = $item->{$columnName};
            }

            //add support for description column, when is not a model
            $columnDescriptionName = $columnName . 'Description';

            if (isset($item->$columnDescriptionName) && $item->$columnDescriptionName)
            {
                $value = $item->$columnDescriptionName;
            }

            if ($ds instanceof \DataSource\Model)
            {
                $model = $ds->getModel();
                $dbColumn = $model->getColumn($columnName);

                if ($dbColumn instanceof \Db\Column)
                {
                    $constantValues = $dbColumn->getConstantValues();

                    if ($constantValues && isset($constantValues [$value]))
                    {
                        $value = $constantValues [$value];
                    }
                }
            }
        }

        $formatter = $column->getFormatter();

        if ($formatter)
        {
            $formatter->setValue($value);
            $value = $formatter->__toString();
        }

        return $value;
    }

}
