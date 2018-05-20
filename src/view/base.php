<?php
namespace View;
/**
 * Html base element
 */
class Base extends \View\View
{

    public function __construct( $id = \NULL, $href = \NULL )
    {
        parent::__construct( 'base' );
        $this->setId( $id )->setAttribute( 'href', $href );
    }

}