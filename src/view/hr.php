<?php

namespace View;

/**
 * Html hr element
 */
class Hr extends \View\View
{

    public function __construct( $id = \NULL, $innerHtml = \NULL, $class = \NULL, $father = \NULL )
    {
        parent::__construct( 'hr', \NULL, $innerHtml, $class, $father );
        $this->setId( $id );
    }

}
