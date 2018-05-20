<?php
namespace View;
/**
 * Paragraf element
 */
class P extends \View\View
{

    public function __construct( $id = \NULL, $innerHtml = \NULL, $class = \NULL )
    {
        parent::__construct( 'p', \NULL, $innerHtml, $class );
        $this->setId( $id );
    }

}