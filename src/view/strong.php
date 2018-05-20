<?php
namespace View;
/**
 * Html strong element
 */
class Strong extends \View\View
{

    public function __construct( $id = \NULL, $innerHtml = \NULL, $class = \NULL )
    {
        parent::__construct( 'strong', \NULL, $innerHtml, $class );
        $this->setId( $id );
    }

}