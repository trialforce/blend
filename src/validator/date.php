<?php
namespace Validator;
/**
 * Validação de data
 */
class Date extends \Validator\Validator
{

    public function validate($value = NULL)
    {
        $error = parent::validate($value);

        if ( mb_strlen( $this->value ) > 0 )
        {
            list($dia, $mes, $ano)  = explode( '/', $this->getValue() );

            if ( !checkdate( $mes, $dia, $ano ) )
            {
                $error[ ] = 'Data inválida.';
            }
        }

        return $error;
    }

}

