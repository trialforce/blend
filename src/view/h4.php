<?php
namespace View;
/**
 * Heading 4
 */
class H4 extends \View\View
{

    public function __construct( $id = \NULL, $innerHtml = \NULL, $class = \NULL )
    {
        parent::__construct( 'h4', \NULL, $innerHtml, $class );
        $this->setId( $id );
    }

}