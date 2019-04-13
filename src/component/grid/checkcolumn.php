<?php

namespace Component\Grid;

/**
 * Checkbox column for grid
 *
 * FIXME: need a complete refactor, remove js from here
 */
class CheckColumn extends \Component\Grid\Column
{

    /**
     * The name/id from the checkbox is the counter of the line
     */
    const NAME_TYPE_LINE = 1;

    /**
     * The name/id from the checkbox is the id from the model
     */
    const NAME_TYPE_OBJ_ATTR = 2;

    /**
     * Define the type of the id
     * NAME_TYPE_LINE the name/id from the checkbox is the counter of the line
     * NAME_TYPE_OBJ_ATTR the name/id from the checkbox is the id from the model
     *
     * @var int
     */
    protected $nameType = self::NAME_TYPE_LINE;

    public function __construct($name = 'check')
    {
        //define a name for security
        $name = $name ? $name : 'check';
        parent::__construct($name, '', Column::ALIGN_LEFT, NULL);
        $this->setExport(FALSE);
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
        $check = new \View\Ext\CheckboxDb('checkAll' . $this->getName());
        $th->addClass(Column::ALIGN_COLAPSE);
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
            $idValue = \Component\Grid\Column::getColumnValue($identificator, $item);

            $nameValue = $line;

            if ($this->nameType == self::NAME_TYPE_OBJ_ATTR && method_exists($item, 'getId'))
            {
                $nameValue = $item->getId();
            }

            $idJs = $this->getIdJs();
            $check = new \View\Checkbox($this->getName() . '[' . $nameValue . ']', $idValue, FALSE, 'checkBox' . $this->getName());
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
                var checked = $(this).prop('checked');
                $('#{$gridId} .checkBox{$name}').prop( 'checked', checked );
                selecteChecks('{$gridId}');
            });";

        return \App::addJs($js);
    }

}
