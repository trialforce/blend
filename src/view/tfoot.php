<?php
namespace View;
/**
 * Table foot html element
 */
class TFoot extends \View\View
{

    public function __construct( $id = NULL, $innerHtml = NULL, $class = NULL )
    {
        parent::__construct( 'tfoot', \NULL, $innerHtml, $class );
        $this->setId( $id );
    }

}