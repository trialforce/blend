<?php
namespace View;
/**
 * Html title element
 */
class Title extends \View\View
{

    public function __construct( $innerHtml = \NULL )
    {
        parent::__construct( 'title', \NULL, $innerHtml );
    }

}