<?php

namespace Component\Grid;

/**
 * Coluna prontina para chave primária
 * FIXME Pesadinha também
 */
class PkColumnEdit extends \Component\Grid\EditColumn
{

    public function __construct($name = NULL, $label = NULL, $align = Column::ALIGN_LEFT, $dataType = \Db\Column::TYPE_INTEGER)
    {
        parent::__construct($name, $label, $align, $dataType);
        $this->setIdentificator(TRUE)->setRender(TRUE);
    }

    public function getValue($item, $line = NULL, \View\View $tr = NULL, \View\View $td = NULL)
    {
        $identificator = $this->getGrid()->getIdentificatorColumn();
        $idValue = \Component\Grid\Column::getColumnValue($identificator, $item);
        $pageUrl = \View\View::getDom()->getPageUrl();
        $url = $this->getEditUrl($item);

        $tr->setAttribute('ondblclick', 'p(\'' . $url . '\');');
        $td->addClass('pkColumnEdit');

        $link = new \View\A('edit_' . $pageUrl . '_' . $idValue, new \View\Ext\Icon('edit'), $url);
        $link->setClass('pkColumnEdit');

        return $link;
    }

}