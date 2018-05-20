<?php

namespace Validator;

/**
 * Validator repeated char
 */
class RepeatedChar extends \Validator\Validator
{

    protected $maxRepetition = 4;

    function getMaxRepetition()
    {
        return $this->maxRepetition;
    }

    function setMaxRepetition( $maxRepetition )
    {
        $this->maxRepetition = $maxRepetition;
        return $this;
    }

    public function validate( $value = NULL )
    {
        $error = parent::validate($value);

        if ( preg_match('/(\w)\1{' . $this->maxRepetition . ',}/', $value) )
        {
            $error[] = "Muitos caracteres repetidos.";
        }

        return $error;
    }

}
