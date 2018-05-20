<?php

namespace View\Ext;

/**
 * Based on AutoNumeric http://www.decorplanit.com/plugin/
 * Limite max and min
 */
class IntInput extends \View\Input
{

    public function __construct( $idName, $value = NULL, $vMax = 99999999999999, $vMin = -99999999999, $class = NULL )
    {
        parent::__construct( $idName, \View\Input::TYPE_NUMBER, $value, $class );

        $vMax = $vMax ? $vMax : 99999999999999;
        $vMin = $vMin ? $vMin : -99999999999;

        $this->setAttribute( 'keyboard', 'numeric' );

        $this->setAttribute( 'data-m-dec', 0 );
        $this->setAttribute( 'data-a-sep', '' );
        $this->setAttribute( 'data-a-dec', '.' );
        $this->setAttribute( 'data-v-max', $vMax );
        $this->setAttribute( 'data-v-min', $vMin );

        $this->addClass( 'integer' );
    }

}
