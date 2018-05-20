<?php

namespace View;

/**
 * Header style element
 */
class Header extends \View\View
{

    public function __construct( $id = \NULL, $innerHtml = \NULL, $class = \NULL )
    {
        parent::__construct( 'header', \NULL, $innerHtml, $class );
        $this->setId( $id );
    }

}
