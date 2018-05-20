<?php

namespace View\Ext;

/**
 * DateInput
 *
 * http://xdsoft.net/jqplugins/datetimepicker/
 */
class TimeInput extends \View\Input
{

    public function __construct( $idName = NULL, $value = null, $class = NULL )
    {
        parent::__construct( $idName, \View\Input::TYPE_TEXT, $value, $class );
        $this->addClass( 'timeinput' );
    }

}
