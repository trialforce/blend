<?php

namespace Component;

use DataHandle\Request;
use View\Td;
use View\Tr;
use Component\Grid\Column;
use View\Table;
use View\Div;

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

        $id = $this->getId();
        $div = new \View\Div('container_' . $id, NULL, 'combo');

        $input[] = new Div('dropDownContainer_' . $id, null, 'dropDownContainer');
        $input[] = $this->labelValue = new \View\InputText('labelField_' . $id, NULL, 'labelValue');

        $this->labelValue->setAttribute('autocomplete', 'new_' . $id);
        $this->labelValue->setData('change', "p('" . $this->getLink('onchange', $id) . "');");
        $this->labelValue->setAttribute('onclick', "comboShowDropdown('$id')");
        $this->labelValue->setAttribute('onKeyUp', "comboTypeWatch( this, event, function(){ comboDoSearch('{$id}'); }, 700 );");
        $this->labelValue->setAttribute('data-invalid-id', $id);
        $this->labelValue->setAttribute('placeholder', 'Pesquisar ...');

        $this->inputValue = new \View\InputText($id, NULL, 'inputValue');
        $this->inputValue->setReadOnly(TRUE);

        $input[] = $this->inputValue;

        $div->append($input);

        //jquery hover works great
        \App::addJs('$("#dropDownContainer_' . $id . '").hover(
          function() {
          },
          function() {
          comboHideDropdown("' . $id . '");
          });
          ');

        $this->makeDefaultSearch($id);

        $this->setContent($div);

        return $div;
    }

    public function makeDefaultSearch($id)
    {
        $page = \View\View::getDom()->getPageUrl();
        $className = str_replace('\\', '-', get_class($this));
        //\App::addJs("p('{$page}/comboOnChange/{$id}/?class=$className&hideCombo=true');");
        \App::addJs("p('{$this->getClassUrl()}/onchange/{$id}?hideCombo=true');");
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

        $class = get_class($this);
        $dataSource = $class::getDataSource();

        $columns = array_values($dataSource->getColumns());
        $indentificatorColumm = $columns[0];
        $labelColumm = $columns[1];

        $dataSource->addExtraFilter(new \Db\Cond($indentificatorColumm->getName() . ' = ?', $value . ''));
        $data = $dataSource->getData();

        if (is_array($data) && isset($data[0]))
        {
            $value = Column::getColumnValue($labelColumm, $data[0]);
            $this->labelValue->setValue($value);
        }
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
     * Mount dropdown on chane
     */
    public function onChange()
    {
        \App::dontChangeUrl();
        $hideCombo = Request::get('hideCombo');
        $layout = \View\View::getDom();
        //TODO ver o porque da barra
        $id = str_replace('/', '', Request::get('v'));

        if (!$hideCombo)
        {
            \View\VIew::getDom()->byId($id)->val('');
        }

        $dataSource = $this->getDataSource();
        $dataSource->setSmartFilter(trim(Request::get('labelField_' . $id)));

        if (!$dataSource->getLimit())
        {
            $dataSource->setLimit(10);
        }

        $data = $dataSource->getData();

        if (is_array($data) && count($data) > 0)
        {
            $columns = array_values($dataSource->getColumns());
            $indentificatorColumm = $columns[0];
            $labelColumm = $columns[1];

            $tr = array();

            foreach ($data as $item)
            {
                $td = NULL;
                $tr[] = $link = new Tr(NULL);

                $i = 0;
                foreach ($columns as $column)
                {
                    if (!$column->getIdentificator())
                    {

                        $td[] = $myTd = new Td('item_column_' . $column->getName() . '_' . $i);

                        $value = $column->getValue($item, $i, $link, $myTd);
                        $myTd->html($value);
                    }
                }

                $i++;

                $value = Column::getColumnValue($indentificatorColumm, $item);
                $label = Column::getColumnValue($labelColumm, $item);

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

}
