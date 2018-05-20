<?php
namespace View;
/**
 * Html italic element
 */
class I extends \View\View
{

    public function __construct( $id = \NULL, $innerHtml = \NULL, $class = \NULL )
    {
        parent::__construct( 'i', \NULL, $innerHtml, $class );
        $this->setId( $id );
    }

}