<?php

namespace Component\Grid;

/**
 * Coluna prontina para chave primária
 */
class PkColumn extends \Component\Grid\Column
{

    public function __construct($name, $label = NULL, $align = Column::ALIGN_LEFT, $dataType = \Db\Column\Column::TYPE_INTEGER)
    {
        parent::__construct($name, $label, $align, $dataType);
        $this->setIdentificator(TRUE)->setRender(FALSE);
    }

}
