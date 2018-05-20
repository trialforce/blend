<?php

namespace View;

/**
 * Html option element.
 * Used inside Select
 */
class Option extends \View\View
{

    public function __construct( $value, $label, $selected = FALSE, $father = NULL )
    {
        parent::__construct( 'option', NULL, NULL, NULL, $father );
        $this->setValue( $value );
        $this->append( $label );

        if ( $selected )
        {
            $this->setAttribute( 'selected', 'selected' );
        }
    }

}
