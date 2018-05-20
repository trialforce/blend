<?php

namespace View;

/**
 * Small
 */
class Small extends \View\View
{

    public function __construct( $id = \NULL, $innerHtml = \NULL, $class = \NULL, $father = \NULL )
    {
        parent::__construct( 'small', \NULL, $innerHtml, $class, $father );
        $this->setId( $id );
    }

}
