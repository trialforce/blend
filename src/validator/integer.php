<?php

namespace Validator;

/**
 * Integer validator
 *
 */
class Integer extends \Validator\Validator
{

    public function validate( $value=NULL)
    {
        $error = parent::validate( $value );

        if ( $this->value )
        {
            if ( !\Validator\Validator::isInteger( $this->value ) )
            {
                $error[] = 'Valor deve ser um número inteiro.';
            }
        }

        return $error;
    }

}
