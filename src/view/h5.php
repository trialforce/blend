<?php
namespace View;
/**
 * Heading 5
 */
class H5 extends \View\View
{

    public function __construct( $id = \NULL, $innerHtml = \NULL, $class = \NULL )
    {
        parent::__construct( 'h5', \NULL, $innerHtml, $class );
        $this->setId( $id );
    }

}