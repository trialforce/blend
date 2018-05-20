<?php

namespace View\Ext;

/**
 * DateInput
 *
 * http://xdsoft.net/jqplugins/datetimepicker/
 */
class DateInput extends \View\Input
{

    public function __construct( $idName = NULL, $value = null, $class = NULL )
    {
        parent::__construct( $idName, \View\Input::TYPE_TEXT, $value, $class );
        $this->addClass( 'dateinput' );
    }

    public function setValue( $value )
    {
        if ( !$value instanceof \Type\DateTime )
        {
            $value = new \Type\Date( $value );
        }

        $value = $value->getValue( \Type\DateTime::MASK_DATE_USER );

        parent::setValue( $value );
    }

}
