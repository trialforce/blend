<?php

namespace View;

/**
 * Html div (block) element
 */
class Div extends \View\View
{

    public function __construct( $id = \NULL, $innerHtml = \NULL, $class = \NULL, $father = \NULL )
    {
        parent::__construct( 'div', \NULL, $innerHtml, $class, $father );
        $this->setId( $id );
    }

}
