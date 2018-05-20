<?php
namespace View;
/**
 * Pre-formated html element
 */
class Pre extends \View\View
{

    public function __construct( $id = \NULL, $innerHtml = \NULL, $class = \NULL )
    {
        parent::__construct( 'pre', \NULL, $innerHtml, $class );
        $this->setId( $id );
    }

    public static function dump( $var )
    {
        return new \View\Pre( NULL, print_r( $var ) );
    }

}