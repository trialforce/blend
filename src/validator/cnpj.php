<?php

namespace Validator;

/**
 * Brazilian CNPJ Validation
 *
 * No Brasil, o Cadastro Nacional da Pessoa Jurídica (acrônimo: CNPJ)
 * é um número único que identifica uma pessoa jurídica e outros tipos
 * de arranjo jurídico sem personalidade jurídica
 * (como condomínios, orgãos públicos, fundos)[1]
 * junto à Receita Federal brasileira (órgão do Ministério da Fazenda).
 */
class Cnpj extends \Validator\Validator
{

    /**
     * Validate CNPJ
     *
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

        if (!self::validaCNPJ($this->value))
        {
            $error[] = 'CNPJ inválido.';
        }

        return $error;
    }

    /**
     * Validate CPNJ
     *
     * @return boolean
     */
    protected static function validaCNPJ($cnpj)
    {
        if (mb_strlen($cnpj) <> 14)
        {
            return FALSE;
        }

        $soma = 0;

        $soma += ($cnpj[0] * 5);
        $soma += ($cnpj[1] * 4);
        $soma += ($cnpj[2] * 3);
        $soma += ($cnpj[3] * 2);
        $soma += ($cnpj[4] * 9);
        $soma += ($cnpj[5] * 8);
        $soma += ($cnpj[6] * 7);
        $soma += ($cnpj[7] * 6);
        $soma += ($cnpj[8] * 5);
        $soma += ($cnpj[9] * 4);
        $soma += ($cnpj[10] * 3);
        $soma += ($cnpj[11] * 2);

        $digito1 = $soma % 11;
        $digito1 = $digito1 < 2 ? 0 : 11 - $digito1;

        $soma = 0;
        $soma += ($cnpj[0] * 6);
        $soma += ($cnpj[1] * 5);
        $soma += ($cnpj[2] * 4);
        $soma += ($cnpj[3] * 3);
        $soma += ($cnpj[4] * 2);
        $soma += ($cnpj[5] * 9);
        $soma += ($cnpj[6] * 8);
        $soma += ($cnpj[7] * 7);
        $soma += ($cnpj[8] * 6);
        $soma += ($cnpj[9] * 5);
        $soma += ($cnpj[10] * 4);
        $soma += ($cnpj[11] * 3);
        $soma += ($cnpj[12] * 2);

        $digito2 = $soma % 11;
        $digito2 = $digito2 < 2 ? 0 : 11 - $digito2;

        if ($cnpj[12] == $digito1 && $cnpj[13] == $digito2)
        {
            return TRUE;
        }
        else
        {
            return FALSE;
        }
    }

}