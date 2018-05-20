<?php
namespace View;
/**
 * Span html element
 */
class Span extends \View\View
{

    public function __construct( $id = \NULL, $innerHtml = \NULL, $class = \NULL )
    {
        parent::__construct( 'span', \NULL, $innerHtml, $class );
        $this->setId( $id );
    }

}