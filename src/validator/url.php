<?php
namespace Validator;
/**
 * Validação de url (endereço web)
 */
class Url extends \Validator\Validator
{
    public function validate($value = null)
    {
        $error = parent::validate($value);

        if ( mb_strlen($value) > 0 && !$this->validaUrl($value) )
        {
            $error[ ] = 'Url inválida.';
        }

        return $error;
    }

    protected function validaUrl($value)
    {
        return filter_var($value, FILTER_VALIDATE_URL ) && preg_match('/(http:|https:)\/\/(.*)/', $value);
    }
}
