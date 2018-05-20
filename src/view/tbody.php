<?php
namespace View;
/**
 * Html table body element
 */
class TBody extends \View\View
{

    public function __construct( $id = \NULL, $innerHtml = \NULL, $class = \NULL )
    {
        parent::__construct( 'tbody', \NULL, $innerHtml, $class );
        $this->setId( $id );
    }

}