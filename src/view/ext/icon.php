<?php

namespace View\Ext;

/**
 * Botstrapicon
 */
class Icon extends \View\I
{

    public function __construct( $icon )
    {
        $icon = str_replace( 'cancel', 'times', $icon );
        $class = 'fa fa-' . $icon;

        parent::__construct( null, null, $class );
    }

}
