<?php

namespace View;

/**
 * Html style element
 */
class Style extends \View\View
{

    public function __construct( $id = \NULL, $innerHtml = \NULL, $class = \NULL )
    {
        parent::__construct( 'style', \NULL, $innerHtml, $class );
        $this->setId( $id );
    }

}
