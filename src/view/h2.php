<?php
namespace View;
/**
 * Secondary heading
 */
class H2 extends \View\View
{

    public function __construct( $id = \NULL, $innerHtml = \NULL, $class = \NULL )
    {
        parent::__construct( 'h2', \NULL, $innerHtml, $class );
        $this->setId( $id );
    }

}