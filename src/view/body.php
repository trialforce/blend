<?php
namespace View;
/**
 * Html body element
 */
class Body extends \View\View
{

    public function __construct( $innerHtml = \NULL, $class = \NULL )
    {
        parent::__construct( 'body', \NULL, $innerHtml, $class );
    }

}