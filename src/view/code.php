<?php

namespace View;

/**
 * Html code element
 */
class Code extends \View\View
{

    public function __construct( $id = \NULL, $innerHtml = \NULL, $class = \NULL, $father = \NULL )
    {
        parent::__construct( 'code', \NULL, $innerHtml, $class, $father );
        $this->setId( $id );
    }

}
