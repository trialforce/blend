<?php

namespace Validator;

/**
 * Validator and type or brasizilian CPF/CNPJ
 */
class CnpjCpf extends \Validator\Validator implements \JsonSerializable
{

    public function getValue()
    {
        return self::unmask($this->value);
    }

    public function setValue($value)
    {
        if ($value instanceof \Type\Generic)
        {
            $value = $value->getValue();
        }

        $this->value = $value;

        return $this;
    }

    public function __toString()
    {
        return self::mask($this->value);
    }

    public function toHuman()
    {
        return self::mask($this->value);
    }

    public function toDb()
    {
        return self::unmask($this->value);
    }

    public function jsonSerialize():mixed
    {
        return $this->toDb();
    }

    public function validate($value = NULL)
    {
        $error = parent::validate($value);
        $this->value = $this->unmask($value);

        if (mb_strlen($this->value) > 0)
        {
            if (mb_strlen($this->value) == 11)
            {
                if (!self::validaCPF($this->value))
                {
                    $error[] = 'CPF digitado inválido. ' .$this->value;
                }
            }
            else if (mb_strlen($this->value) > 13)
            {
                if (!self::validaCNPJ($this->value))
                {
                    $error[] = 'CNPJ digitado inválido. '.$this->value;
                }
            }
            else
            {
                $error[] = 'Valor digitado inválido. '.$this->value;
            }
        }

        return $error;
    }

    /**
     * Faz a validação do CPF
     *
     * @return boolean
     */
    public static function validaCPF($cpf)
    {
        // Verifica se nenhuma das sequências abaixo foi digitada, caso seja, retorna falso
        if (mb_strlen($cpf) != 11 || self::contaRepeteNumero($cpf, 11))
        {
            return false;
        }
        else
        {   // Calcula os números para verificar se o CPF é verdadeiro
            for ($t = 9; $t < 11; $t++)
            {
                for ($d = 0, $c = 0; $c < $t; $c++)
                {
                    $d += $cpf[$c] * (($t + 1) - $c);
                }

                $d = ((10 * $d) % 11) % 10;

                if ($cpf[$c] != $d)
                {
                    return false;
                }
            }

            return true;
        }
    }

    /**
     * Faz a validação do CNPJ
     *
     * @return boolean
     */
    public static function validaCNPJ($cnpj)
    {
        if (mb_strlen($cnpj) <> 14 || self::contaRepeteNumero($cnpj, 14))
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

        return ( $cnpj[12] == $digito1 && $cnpj[13] == $digito2 );
    }

    /**
     * retorna se caracter se repete n vezes
     *
     * @param int $numero
     * @param int $repeticoes
     * @return boolean
     */
    protected static function contaRepeteNumero($numero, $repeticoes)
    {
        for ($i = 0; $i < $repeticoes; $i++)
        {
            if (preg_match('/' . $i . '{' . $repeticoes . '}/', $numero))
            {
                return TRUE;
            }
        }

        return FALSE;
    }

    /**
     * Coloca máscara num CPF/CNPJ
     *
     * Chupinhado de http://webmarcos.net/2008/11/12/funcao-para-formatar-cpfcnpj/
     *
     * @param mixed $value
     * @return string
     */
    public static function mask($value)
    {
        if (mb_strlen(trim($value)) > 10)
        {
            $value = self::unmask($value);
            $mascara = mb_strlen($value) <= 11 ? '###.###.###-##' : '##.###.###/####-##';
            $indice = 0;

            for ($i = 0; $i < mb_strlen($mascara); $i++)
            {
                if ($mascara[$i] == '#')
                {
                    if (isset($value[$indice]))
                    {
                        $mascara[$i] = $value[$indice];
                        $indice++;
                    }
                }
            }
        }
        else
        {
            $mascara = $value.'';
        }

        return $mascara;
    }

    public static function get($value = null, $column = null)
    {
        return new \Type\CpfCnpj($value, $column);
    }

    public static function value($value = null)
    {
        return \Type\CpfCnpj::get(null, $value)->getValue();
    }

}
