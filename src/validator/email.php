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

        if (mb_strlen($this->value.'') > 0 && !$this->validaEmail())
        {
            $error[] = 'E-mail inválido.';
        }

        return $error;
    }

    protected function validaEmail()
    {
        return filter_var($this->value, FILTER_VALIDATE_EMAIL);
    }

    public static function obfuscate($email)
    {
        $em = explode('@', $email);
        $name = implode('@', array_slice($em, 0, count($em) - 1));
        $len = floor(strlen($name) / 2);

        return substr($name, 0, $len) . str_repeat('*', $len) . '@' . end($em);
    }

}
