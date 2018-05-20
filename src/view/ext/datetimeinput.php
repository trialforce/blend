<?php

namespace View\Ext;

/**
 * DateInput
 */
class DateTimeInput extends \View\Input
{

    public function __construct( $idName = NULL, $value = null, $class = NULL )
    {
        parent::__construct( $idName, \View\Input::TYPE_TEXT, $value, $class );
        $this->addClass( 'datetimeinput' );
    }

    public function setValue( $value )
    {
        if ( !$value instanceof \Type\DateTime )
        {
            $value = new \Type\DateTime( $value );
        }

        $value = $value->getValue( \Type\DateTime::MASK_TIMESTAMP_USER );

        parent::setValue( $value );
    }

}
