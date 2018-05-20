<?php

namespace View;

/**
 * Html input text
 */
class InputText extends \View\Input
{

    public function __construct( $idName = NULL, $value = NULL, $class = NULL )
    {
        parent::__construct( $idName, \View\Input::TYPE_TEXT, $value, $class );
    }

}
