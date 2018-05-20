<?php
namespace View;
/**
 * Html label element
 */
class Label extends \View\View
{

    public function __construct( $id = \NULL, $for = \NULL, $label = \NULL, $class = \NULL )
    {
        parent::__construct( 'label', \NULL, $label, $class );
        $this->setId( $id )->setAttribute( 'for', $for );
    }

}