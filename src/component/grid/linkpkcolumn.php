<?php

namespace Component\Grid;

class LinkPkColumn extends \Component\Grid\LinkColumn
{

    public function __construct( $name, $label = \NULL, $align = Column::ALIGN_LEFT, $dataType = \Db\Column::TYPE_VARCHAR )
    {
        parent::__construct( $name, $label, $align, $dataType );
        $this->setOrder( FALSE )->setFilter( FALSE );
    }

}
