<?php
namespace View;
/**
 * Html legend element
 */
class Legend extends \View\View
{

    public function __construct( $id = \NULL, $innerHtml = \NULL, $class = \NULL )
    {
        parent::__construct( 'legend', \NULL, $innerHtml, $class );
        $this->setId( $id );
    }

}