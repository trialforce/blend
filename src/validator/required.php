<?php

namespace Validator;

/**
 * Validate required field
 */
class Required extends \Validator\Validator
{

    public function validate( $value = NULL )
    {
        parent::validate($value);
        $error = array();
        
        if ( is_null( $this->value ) || $this->value === '' || empty( $this->value ) )
        {
            $error[] = 'Campo requerido.';
        }

        return $error;
    }

}
