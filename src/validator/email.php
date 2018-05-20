<?php

namespace Validator;

/**
 * E-mail validation
 */
class Email extends \Validator\Validator
{

    public function validate($value = NULL)
    {
        $error = parent::validate($value);

        if (mb_strlen($this->value) > 0 && !$this->validaEmail())
        {
            $error[] = 'E-mail inválido.';
        }

        return $error;
    }

    protected function validaEmail()
    {
        return filter_var($this->value, FILTER_VALIDATE_EMAIL);
    }

}