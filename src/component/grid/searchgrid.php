<?php

namespace Component\Grid;

/**
 * Search grid used in search crud forms
 *
 * @author eduardo
 */
class SearchGrid extends \Component\Grid\Grid
{

    /**
     * Call interface functions.
     * Used for optimizations
     *
     * @var boolean
     */
    protected $callInterfaceFunctions = TRUE;

    /**
     * Search field
     * @var array
     */
    protected $searchField = NULL;

    public function __construct($id = NULL, $dataSource = NULL, $class = 'grid', $columns = NULL)
    {
        $myId = $id ? $id : get_class($this);
        parent::__construct($myId, $dataSource, $class);
        $this->setColumns($columns);
        $this->setSearchField(new \Component\Grid\SearchField($this, 'searchField'));
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

        $div = $this->createTable();
        $searchField = $this->getSearchField();

        if ($searchField instanceof SearchField)
        {
            $searchField = $searchField->onCreate();
        }

        $content = array($searchField, $div);

        $this->setContent($content);

        return $content;
    }

    public function getSearchField()
    {
        return $this->searchField;
    }

    public function setSearchField($searchField)
    {
        //make it work from json serialization
        if ($searchField)
        {
            $searchField->setGrid($this);
        }

        $this->searchField = $searchField;

        return $this;
    }

    public function getCallInterfaceFunctions()
    {
        return $this->callInterfaceFunctions;
    }

    public function setCallInterfaceFunctions($callInterfaceFunctions)
    {
        $this->callInterfaceFunctions = $callInterfaceFunctions;
    }

    protected function createTd(\Component\Grid\Column $column, $index, $item, $tr)
    {
        $dom = \View\View::getDom();
        $afterGridCreateCell = FALSE;

        if ($this->getCallInterfaceFunctions())
        {
            $afterGridCreateCell = $dom instanceof \Page\AfterGridCreateCell;
        }

        $td = parent::createTd($column, $index, $item, $tr);

        if ($afterGridCreateCell)
        {
            \View\View::getDom()->afterGridCreateCell($column, $item, $index, $tr, $td);
        }

        return $td;
    }

    protected function createTr($columns, $index, $item)
    {
        $beforeGridCreateRow = false;
        $afterGridCreateRow = false;
        $dom = \View\View::getDom();

        if ($this->getCallInterfaceFunctions())
        {
            $beforeGridCreateRow = $dom instanceof \Page\BeforeGridCreateRow;
            $afterGridCreateRow = $dom instanceof \Page\AfterGridCreateRow;
        }

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
                $column->setGrid($this);

                //jump column that not render
                if (!$column->getRender())
                {
                    continue;
                }

                $class = $orderBy == $column->getName() ? 'order-by' : '';

                $cols[] = $col = new \View\Col('col-' . $column->getName(), NULL, null, $class);
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

}