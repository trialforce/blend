<?php
namespace View;
/**
 * Main heading
 */
class H1 extends \View\View
{

    public function __construct( $id = \NULL, $innerHtml = \NULL, $class = \NULL )
    {
        parent::__construct( 'h1', \NULL, $innerHtml, $class );
        $this->setId( $id );
    }

}