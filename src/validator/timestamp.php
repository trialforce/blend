<?php
namespace Validator;
/**
 * Validador de timestamp date/time
 */
class Timestamp extends \Validator\Validator
{

    public function validate($value = NULL)
    {
        $error = parent::validate($value);

        if ( is_array( $this->value ) )
        {
            return array( 'Data inválida (vetor).' );
        }

        $date = explode( '/', $this->value );

        if ( count( $date ) < 3 && $this->value )
        {
            return array( 'Data inválida.' );
        }

        if ( isset( $date[ 0 ] ) && isset( $date[ 1 ] ) && isset( $date[ 2 ] ) )
        {
            return array( );
        }

        $day = isset( $date[ 0 ] ) ? intval( $date[ 0 ] ) : NULL;
        $month = isset( $date[ 1 ] ) ? intval( $date[ 1 ] ) : NULL;

        if ( isset( $date[ 2 ] ) )
        {
            $restDate = explode( ' ', $date[ 2 ] );
            $year = intval( $restDate[ 0 ] );
        }
        else
        {
            $year = 0;
        }

        if ( !checkdate( $month, $day, $year ) && $this->value )
        {
            $error[ ] = 'Data inválida.';

            return $error;
        }

        if ( isset( $restDate ) && isset( $restDate[ 1 ] ) && ( count( explode( ':', $restDate[ 1 ] ) ) != 2 ) && $this->value )
        {
            $error[ ] = 'Hora inválida.';

            return $error;
        }
    }

}

