<?php
namespace View;
/**
 * Html fieldset element
 */
class Fieldset extends \View\View
{

    public function __construct( $id = NULL, $innerHtml = NULL, $class = NULL )
    {
        parent::__construct( 'fieldset', \NULL, $innerHtml, $class );
        $this->setId( $id );
    }

}