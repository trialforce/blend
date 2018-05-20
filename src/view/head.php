<?php
namespace View;
/**
 * Html head element
 */
class Head extends \View\View
{

    public function __construct( $idName = \NULL, $innerHtml = \NULL )
    {
        parent::__construct( 'head', $idName, $innerHtml );
    }

}