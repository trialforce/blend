<?php

namespace Validator;

/**
 * File name validation
 */
class FileName extends \Validator\Validator
{

    public function validate($value = NULL)
    {
        $error = parent::validate($value);

        $file = \Type\Text::get($value)->toFile('-')->__toString();

        if ($value != $file)
        {
            $error[] = 'Nome inválido.';
        }

        return $error;
    }

}