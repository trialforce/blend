<?php

namespace Validator;

/**
 * Brasilian CPF
 * Cadastro de pessoas físicas
 */
class Cpf extends \Validator\Validator
{

    /**
     * Validate CPF
     * @param string $value
     * @return string
     */
    public function validate($value = NULL)
    {
        $error = parent::validate($value);
        $this->value = parent::unmask($this->value);

        if (mb_strlen($this->value) == 0)
        {
            return;
        }

        if (!self::validaCPF($this->value))
        {
            $error[] = 'CPF inválido.';
        }

        return $error;
    }

    /**
     * Validate CPF
     *
     * @return boolean
     */
    protected static function validaCPF($cpf)
    {
        // Verifica se nenhuma das sequências abaixo foi digitada, caso seja, retorna falso
        if (mb_strlen($cpf) != 11)
        {
            return false;
        }
        else
        {   // Calcula os números para verificar se o CPF é verdadeiro
            for ($t = 9; $t < 11; $t++)
            {
                for ($d = 0, $c = 0; $c < $t; $c++)
                {
                    $d += $cpf{$c} * (($t + 1) - $c);
                }

                $d = ((10 * $d) % 11) % 10;

                if ($cpf{$c} != $d)
                {
                    return false;
                }
            }

            return true;
        }
    }

}