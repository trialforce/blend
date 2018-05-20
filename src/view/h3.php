<?php
namespace View;
/**
 * Heading 3
 */
class H3 extends \View\View
{

    public function __construct( $id = \NULL, $innerHtml = \NULL, $class = \NULL )
    {
        parent::__construct( 'h3', $id, $innerHtml, $class );
    }

}