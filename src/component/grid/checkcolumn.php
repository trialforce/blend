<?php

namespace Component\Grid;

/**
 * Coluna de checagem da grid
 */
class CheckColumn extends \Component\Grid\Column
{

    const NAME_TYPE_LINE = 1;
    const NAME_TYPE_OBJ_ATTR = 2;

    protected $nameType = self::NAME_TYPE_LINE;

    public function __construct($name = 'check')
    {
        //security
        $name = $name ? $name : 'check';

        parent::__construct($name, '', Column::ALIGN_LEFT, NULL);
        $this->setExport(FALSE);
        $this->setWidth('3%');
    }

    function getNameType()
    {
        return $this->nameType;
    }

    function setNameType($nameType)
    {
        $this->nameType = $nameType;
    }

    public function getHeadContent(\View\View $tr, \View\View $th)
    {
        $gridId = $this->getIdJs();
        $name = $this->getName();

        $js = "
            function selecteChecks(gridName)
            {
                $('#'+gridName+'Table .checkBoxcheck').each( function()
                {
                    if ( $(this).prop('checked') === true )
                    {
                        $(this).parent().parent().addClass('select');
                    }
                    else
                    {
                        $(this).parent().parent().removeClass('select');
                    }
                });
            }

            function selecteCheck(elementId)
            {
                var element = $('#' + elementId);
                var selecionado = !element.prop('checked');
                element.prop('checked', selecionado);

                $('#checkAllcheck').prop('checked', false);
            }";

        \App::addJs($js);

        $check = new \View\Ext\CheckboxDb('checkAll' . $this->getName());
        $th->addClass(Column::ALIGN_LEFT);
        $this->getJs();

        return $check;
    }

    public function getValue($item, $line = NULL, \View\View $tr = NULL, \View\View $td = NULL)
    {
        $check = '';
        $makeCheck = true;

        //verify objetct
        if (method_exists($item, 'getMakeGridCheck'))
        {
            $makeCheck = $item->getMakeGridCheck();
        }

        if ($makeCheck)
        {
            $identificator = $this->getGrid()->getIdentificatorColumn();
            $idValue = \DataSource\Grab::getUserValue($identificator, $item);

            $nameValue = $line;

            if ($this->nameType == self::NAME_TYPE_OBJ_ATTR && method_exists($item, 'getId'))
            {
                $nameValue = $item->getId();
            }

            $idJs = $this->getIdJs();
            $check = new \View\Checkbox($this->getName() . '[' . $nameValue . ']', $idValue, FALSE, 'checkBox' . $this->getName());
            $check->addStyle('margin', '0 6px');
            $check->click("selecteCheck(this.id); selecteChecks('{$idJs}');");
        }


        return $check;
    }

    protected function getIdJs()
    {
        return str_replace('\\', '\\\\', addslashes($this->grid->getid()));
    }

    protected function getJs()
    {
        $gridId = $this->getIdJs();
        $name = $this->getName();

        $js = "
            $('#{$gridId} #checkAll{$name}').click( function ()
            {
                var selecionado = $(this).prop('checked');
                $('#{$gridId} .checkBox{$name}').prop( 'checked', selecionado );
                selecteChecks('{$gridId}');
            });";

        return \App::addJs($js);
    }

}
