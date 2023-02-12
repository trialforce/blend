<?php

namespace View;

/**
 * Radio html element
 * TODO //need refactor to name and id separated
 */
class Radio extends \View\Input
{

    public function __construct( $idName = \NULL, $value = 1, $class = \NULL, $checked = false )
    {
        parent::__construct( $idName, \View\Input::TYPE_RADIO, $value, $class );
        $this->setChecked($checked);
    }

    public function setChecked( $checked )
    {
        if ( $checked )
        {
            $this->setAttribute( 'checked', 'checked' );
        }
        else
        {
            $this->removeAttribute( 'checked' );
        }
    }

}
