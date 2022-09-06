<?php

namespace Validator;

/**
 * Brazilian Cep Validator
 *
 * O Código de Endereçamento Postal é um conjunto numérico constituído de oito algarismos,
 * cujo objetivo principal é orientar e acelerar o encaminhamento,
 * o tratamento e a distribuição de objetos de correspondência,
 * por meio da sua atribuição a localidades, logradouros,
 * unidades dos Correios, serviços, órgãos públicos, empresas e edifícios.
 *
 * http://www.correios.com.br/servicos/cep/cep_estrutura.cfm
 *
 */
class Cep extends \Validator\Validator
{

    /**
     * Validate CEP
     * @param string $value
     * @return string
     */
    public function validate($value = NULL)
    {
        $error = parent::validate($value);
        //remove unnecessary characters
        $this->value = str_replace(array('-', '.'), '', $this->value);

        if (mb_strlen($this->value) > 0 && !$this->validateCep())
        {
            $error[] = 'Cep digitado é inválido';
        }

        return $error;
    }

    /**
     * Validate CEP
     * @return boolean
     */
    protected function validateCep()
    {
        if (preg_match("/^[0-9]{8}$/", $this->value))
        {
            return TRUE;
        }
        else
        {
            return FALSE;
        }
    }

    public static function mask($value)
    {
        $cep = null;

        if ($value)
        {
            $value = \Validator\Validator::unmask($value);
            $cep = substr($value, 0, 5) . '-' . substr($value, 5, 3);
        }

        return $cep;
    }

}
