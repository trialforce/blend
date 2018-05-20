<?php
namespace View;
/**
 * Html meta tag
 */
class Meta extends \View\View
{

    public function __construct( $name = NULL, $innerHtml = NULL )
    {
        parent::__construct( 'meta' );
        $this->setAttribute( 'name', $name );
        $this->append( $innerHtml );
    }

}