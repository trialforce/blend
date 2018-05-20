<?php
namespace View;
/**
 * Heading 6
 */
class H6 extends \View\View
{

    public function __construct( $id = \NULL, $innerHtml = \NULL, $class = \NULL )
    {
        parent::__construct( 'h6', \NULL, $innerHtml, $class );
        $this->setId( $id );
    }

}