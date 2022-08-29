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
     * Validate CNPJ
     *
     * @return boolean
     */
    protected static function validaCNPJ($cnpj)
    {
        return \Validator\CnpjCpf::validaCNPJ($cnpj);
    }

    /**
     * Create ranndom CNPJ
     *
     * @return string
     */
    public static function createRandom()
    {
        $n1 = rand(0, 9);
        $n2 = rand(0, 9);
        $n3 = rand(0, 9);
        $n4 = rand(0, 9);
        $n5 = rand(0, 9);
        $n6 = rand(0, 9);
        $n7 = rand(0, 9);
        $n8 = rand(0, 9);
        $n9 = 0;
        $n10 = 0;
        $n11 = 0;
        $n12 = 1;
        $d1 = $n12 * 2 + $n11 * 3 + $n10 * 4 + $n9 * 5 + $n8 * 6 + $n7 * 7 + $n6 * 8 + $n5 * 9 + $n4 * 2 + $n3 * 3 + $n2 * 4 + $n1 * 5;
        $d1 = 11 - (self::mod($d1, 11) );

        if ($d1 >= 10)
        {
            $d1 = 0;
        }

        $d2 = $d1 * 2 + $n12 * 3 + $n11 * 4 + $n10 * 5 + $n9 * 6 + $n8 * 7 + $n7 * 8 + $n6 * 9 + $n5 * 2 + $n4 * 3 + $n3 * 4 + $n2 * 5 + $n1 * 6;
        $d2 = 11 - (self::mod($d2, 11) );

        if ($d2 >= 10)
        {
            $d2 = 0;
        }

        return $n1 . $n2 . $n3 . $n4 . $n5 . $n6 . $n7 . $n8 . $n9 . $n10 . $n11 . $n12 . $d1 . $d2;
    }

    private static function mod($dividendo, $divisor)
    {
        return round($dividendo - (floor($dividendo / $divisor) * $divisor));
    }

}
