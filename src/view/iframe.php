<?php
namespace View;
/**
 * IFrame element
 */
class IFrame extends \View\View
{

    public function __construct( $id = NULL, $src = NULL, $class = NULL )
    {
        parent::__construct( 'iframe' );
        $this->setAttribute( "id", $id )->setAttribute( "src", $src )->setClass( $class );
    }

}