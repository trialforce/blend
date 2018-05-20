<?php
namespace View;
/**
 * Bold html element
 */
class B extends \View\View
{

    public function __construct( $id = \NULL, $innerHtml = \NULL, $class = \NULL )
    {
        parent::__construct( 'b', \NULL, $innerHtml, $class );
        $this->setId( $id );
    }

}