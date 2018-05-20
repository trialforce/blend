<?php
namespace View;
/**
 * Li html element
 */
class Li extends \View\View
{

    public function __construct( $id = \NULL, $innerHtml = \NULL, $class = \NULL )
    {
        parent::__construct( 'li', \NULL, $innerHtml, $class );
        $this->setId( $id );
    }

}