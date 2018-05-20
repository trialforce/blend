<?php
namespace View;
/**
 * Html table caption element
 */
class Caption extends \View\View
{

    public function __construct( $id = \NULL, $innerHtml = \NULL, $class = \NULL )
    {
        parent::__construct( 'caption', \NULL, $innerHtml, $class );
        $this->setId( $id );
    }

}