<?php

namespace Component;

use DataHandle\Request;
use View\Td;
use View\Tr;
use View\Table;
use View\Div;

/**
 * Extended component for Combo/Autocomplete
 */
abstract class Combo extends \Component\Component
{

    /**
     *
     * @var \View\InputText
     */
    protected $inputValue;

    /**
     *
     * @var \View\InputText
     */
    protected $labelValue;

    public function __construct($id = null)
    {
        parent::__construct($id);
    }

    public function onCreate()
    {
        //avoid double creation
        if ($this->isCreated())
        {
            return $this->getContent();
        }

        $name = $this->getId();
        $id = $this->getId();

        if (stripos($id, '['))
        {
            $id = str_replace(array('[', ']'), '', $id);
        }

        $div = new \View\Div('container_' . $id, NULL, 'combo');

        $input[] = new Div('dropDownContainer_' . $id, null, 'dropDownContainer');
        $input[] = $this->labelValue = new \View\InputText('labelField_' . $id, NULL, 'labelValue');

        $this->labelValue->setAttribute('autocomplete', 'new_' . $id);
        $this->labelValue->setData('change', "p('" . $this->getLink('mountDropDown', $id) . "');");
        $this->labelValue->setAttribute('onclick', "comboInputClick('$id', this)");
        $this->labelValue->setAttribute('onKeyUp', "comboTypeWatch( this, event, function(){ comboDoSearch('{$id}'); }, 700 );");
        $this->labelValue->setAttribute('data-invalid-id', $id);
        $this->labelValue->setAttribute('placeholder', 'Pesquisar ...');

        $this->inputValue = new \View\InputText($name, NULL, 'inputValue');
        $this->inputValue->setReadOnly(TRUE);

        $input[] = $this->inputValue;

        $div->append($input);

        $this->makeDefaultSearch($id);
        $this->setContent($div);

        return $div;
    }

    public function makeDefaultSearch($id)
    {
        $page = \View\View::getDom()->getPageUrl();
        $className = str_replace('\\', '-', get_class($this));
        $module = \DataHandle\Config::get('use-module') ? 'component/' : '';
        \App::addJs("p('$module{$this->getClassUrl()}/mountDropDown/{$id}?hideCombo=true');");
    }

    public function hideValue()
    {
        if ($this->getContent())
        {
            $this->getContent()->addClass('hideValue');
        }
    }

    /**
     * Mount the data of the combo
     */
    public abstract function getDataSource();

    /**
     * Define the value of combo
     *
     * @param string $value
     * @return \Component\Combo
     */
    public function setValue($value)
    {
        if (!$value)
        {
            return $this;
        }

        $this->inputValue->setValue($value);
        $item = $this->getFirstDataItem($value);

        if ($item)
        {
            $value = \DataSource\Grab::getUserValue($this->getLabelColumn(), $item);
            $this->labelValue->setValue($value);
        }
    }

    protected function getInstanceDataSource()
    {
        $class = get_class($this);
        $dataSource = $class::getDataSource();

        return $dataSource;
    }

    protected function getLabelColumn()
    {
        $dataSource = $this->getInstanceDataSource();
        $columns = array_values($dataSource->getColumns());
        return $columns[1];
    }

    protected function getFirstDataItem($value = null)
    {
        $dataSource = $this->getInstanceDataSource();
        $columns = array_values($dataSource->getColumns());
        $indentificatorColumm = $columns[0];

        $where = new \Db\Where($indentificatorColumm->getName(), '=', $value . '');
        $dataSource->addExtraFilter($where);
        $data = $dataSource->getData();

        if (isIterable($data) && isset($data[0]))
        {
            return $data[0];
        }

        return null;
    }

    /**
     * Return the value
     *
     * @return string
     */
    public function getValue()
    {
        return $this->inputValue->getValue();
    }

    /**
     * Ajax funcion to fill the label using value
     * Example:
     * \App::addJs("p('combo-cliente/fillLabelByValue/idCliente');");
     */
    public function fillLabelByValue()
    {
        \App::dontChangeUrl();
        $idValue = Request::get('v');
        $value = Request::get($idValue);

        if ($value)
        {
            $item = $this->getFirstDataItem($value);

            if ($item)
            {
                $labelValue = \DataSource\Grab::getUserValue($this->getLabelColumn(), $item);
                $this->byId('labelField_' . $idValue)->val($labelValue);
            }
        }
    }

    /**
     * Mount dropdown on change
     */
    public function mountDropDown()
    {
        \App::dontChangeUrl();
        $hideCombo = Request::get('hideCombo');
        $layout = \View\View::getDom();
        //TODO verify why the bar comes in the post
        $id = str_replace('/', '', Request::get('v'));

        if (!$hideCombo)
        {
            \View\VIew::getDom()->byId($id)->val('');
        }

        $dataSource = $this->getDataSource();
        $searchValue = trim(Request::get('labelField_' . $id));

        $this->filterData($dataSource, $searchValue);

        if (!$dataSource->getLimit())
        {
            $dataSource->setLimit(10);
        }

        $data = $dataSource->getData();

        if (isIterable($data) && count($data) > 0)
        {
            $columns = array_values($dataSource->getColumns());
            $indentificatorColumm = $columns[0];
            $labelColumm = $columns[1];

            $tr = array();

            foreach ($data as $item)
            {
                $i = 0;

                $td = NULL;
                $tr[] = $link = $this->createTr($i, $item);

                foreach ($columns as $column)
                {
                    if (!$column->getIdentificator())
                    {
                        $td[] = $myTd = $this->createTd($i, $column, $item);

                        $value = $column->getValue($item, $i, $link, $myTd);
                        $myTd->html($value);
                    }
                }

                $i++;

                $value = \DataSource\Grab::getUserValue($indentificatorColumm, $item);
                $label = \DataSource\Grab::getUserValue($labelColumm, $item);

                $link->html($td);
                $link->click("comboSelectItem('{$id}', '{$value}', \"{$label}\", this );")->setTabIndex(0);
                $link->setAttribute('onkeydown', 'if(event.which == 13) { $(this).trigger(\'click\'); }');
            }

            $table = new Table(null, $tr);

            $itens[] = new Div(null, $table);
        }
        else
        {
            $itens[] = new Div(null, 'Nenhum registro encontrado!');
        }

        $container = $layout->byId('dropDownContainer_' . $id);
        $container->html($itens);

        if ($hideCombo)
        {
            $container->hide();
        }
        else
        {
            $container->show();
        }
    }

    /**
     * Create a Tr element for the select table
     * @param int $row
     * @param object $item
     * @return Tr
     */
    public function createTr($row, $item)
    {
        return new Tr(NULL);
    }

    /**
     * Create a Td element for the select table
     * @param int $row
     * @param string $column
     * @param object $item
     * @return Td
     */
    public function createTd($row, $column, $item)
    {
        return new Td('item_column_' . $column->getName() . '_' . $row);
    }

    /**
     * Apply the filter to the datasource
     *
     * A separated filterData to be extended and overwrited
     * @param \DataSource\DataSource $dataSource
     * @param string $searchText
     * @return \DataSource\DataSource
     */
    public function filterData(\DataSource\DataSource $dataSource, $searchText)
    {
        $dataSource->setSmartFilter($searchText);

        return $dataSource;
    }

}
