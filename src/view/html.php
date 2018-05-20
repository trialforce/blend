<?php
namespace View;
/**
 * Html main element
 */
class Html extends \View\View
{

    public function __construct( $innerHtml = \NULL )
    {
        parent::__construct( 'html', \NULL, $innerHtml );
    }

}