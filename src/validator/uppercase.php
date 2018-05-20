<?php
namespace Validator;
/**
 * Validador de upper case
 */
class UpperCase extends \Validator\Validator
{

    public function validate($value = NULL)
    {
        $error = parent::validate($value);

        if ( mb_strlen( $this->value ) > 0 && $this->value == mb_strtoupper( $this->value ) )
        {
            $error[] = 'Todas letras em maísculas. Verifique a digitação.';
        }

        return $error;
    }

}

