<?php

namespace View\Ext;

class AInput extends \View\A
{

    public function setValue( $value )
    {
        $input = new \View\Input( $this->getId(), \View\Input::TYPE_HIDDEN, $value );
        $input->setId( NULL );
        $this->html( array( $value, $input ) );
        $this->setTarget( self::TARGET_BLANK );
        return $this->setHref( $value );
    }

}
