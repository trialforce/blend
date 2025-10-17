<?php

namespace Validator;

/**
 * Validador de telefone
 */
class Phone extends \Validator\Validator
{

    private $listaDD = array(11, 12, 13, 14, 15, 16, 17, 18, 19,
        21, 22, 24, 27, 28,
        31, 32, 33, 34, 35, 37, 38,
        41, 42, 43, 44, 45, 46, 47, 48, 49,
        51, 53, 54, 55,
        61, 62, 63, 64, 65, 66, 67, 68, 69,
        71, 73, 74, 75, 77, 79,
        81, 82, 83, 84, 85, 86, 87, 88, 89,
        91, 92, 93, 94, 95, 96, 97, 98, 99);

    /**
     * Verifica se o telefone é composto somente de números ()-
     */
    public function validaCaracteres($value)
    {
        return strpbrk($value, '0123456789()-') !== false;
    }

    /**
     * Verifica via expressão regular se tem somente números de 10 a 12 de tamanho
     * @return boolean
     */
    protected function validaFone()
    {
        if (preg_match('/^[0-9]{10,12}$/', $this->value))
        {
            return TRUE;
        }
        else
        {
            return FALSE;
        }
    }

    /**
     * Verifica todos números iguais
     *
     * @param string $value
     * @param string $igual
     * @return bool
     */
    protected function verificaIguais($value, $igual = '0')
    {
        return substr_count($value, $igual) == strlen($value);
    }

    public function validate($value = NULL)
    {
        $error = parent::validate($value);

        //não valida telefones vazios okay?
        if (mb_strlen($this->value . '') == 0)
        {
            return null;
        }

        //caso for um número internacional a gente nem mexe com ele
        if (str_starts_with($this->value, '+'))
        {
            return null;
        }

        if (!$this->validaCaracteres($value))
        {
            $error[] = 'Telefone inválido, só pode conter números, parênteses e traço!';

            return $error;
        }

        $this->value = self::unmask($this->value);

        if ($this->verificaIguais($this->value, '0') || $this->verificaIguais($this->value, '1') || $this->verificaIguais($this->value, '2') || $this->verificaIguais($this->value, '3') || $this->verificaIguais($this->value, '4') || $this->verificaIguais($this->value, '5') || $this->verificaIguais($this->value, '6') || $this->verificaIguais($this->value, '7') || $this->verificaIguais($this->value, '8') || $this->verificaIguais($this->value, '9'))
        {
            $error[] = 'Inválido, todos digitos iguais!';

            return $error;
        }

        if (mb_strlen($this->value) > 0 && !$this->validaFone())
        {
            $error[] = 'Inválido (De 10 a 12 números).';
        }
        else
        {
            if (str_starts_with($this->value, '0'))
            {
                return $error;
            }

            //If number isnt in DDD list.
            if (!in_array($this->value[0] . $this->value[1], $this->listaDD))
            {
                $error[] = 'DDD inválido.';
            }

            $numero = substr($this->value, 2, 9);

            if (strlen($numero) == 9 && $numero[0] != 9)
            {
                $error[] = 'Celular deve iniciar com 9.';
            }

            if (strlen($numero) < 8)
            {
                $error[] = 'Número incompleto.';
            }
        }

        return $error;
    }

    /**
     * Coloca máscara num telefone
     *
     * @param string $value
     * @return string
     */
    public static function mask($value)
    {
        return self::fixNumber($value, 51, true);
    }

    /**
     * Fix brasilian phone number
     *
     * @param ?string $number
     * @param string $defaultPrefix
     * @param boolean $format
     * @return string
     */
    public static function fixNumber($number, $defaultPrefix = 51, $format = false)
    {
        $number = $number . '';

        //tira o +55 da frente caso aconteça
        if (str_starts_with($number, '+55'))
        {
            $number = substr($number, 3, strlen($number));
        }

        //caso for um número internacional a gente nem mexe com ele
        if (str_starts_with($number, '+'))
        {
            return $number;
        }

        $number = Validator::unmask($number);
        $nove = 9;
        //lista de prefixos de paises suportados
        $prefixosPaises = array(55);
        //prefixo que identifica se eh numero de celular
        $prefixosCelular = array(8, 9);
        $number800 = preg_replace('/\s/', '', $number);
        $number = (int) trim(str_replace(array('(', ')', '-', '#', 'EX'), array('', '', '', '', ''), $number));

        if (strlen($number) < 3)
        {
            return '';
        }

        if (str_starts_with($number800, '0'))
        {
            if ($format && strlen($number800) == 11)
            {
                $mask = str_ireplace('9', '%s', '9999-999-9999');
                $number800 = vsprintf($mask, str_split($number800));
            }

            return $number800;
        }

        // caso onde o numero possui o prefixo do pais
        if (strlen($number) > 11 && in_array(substr($number, 0, 2), $prefixosPaises))
        {
            $number = (int) substr($number, 2);
        }

        // coloca o 9 na frente caso numero ainda nao possua
        if (strlen($number) == 8 && in_array(substr($number, 0, 1), $prefixosCelular))
        {
            $number = $defaultPrefix . $nove . $number;
        }

        // caso onde temos o prefixo sem o 9 na frente
        if (strlen($number) == 10 && in_array(substr($number, 2, 1), $prefixosCelular))
        {
            $number = substr($number, 0, 2) . $nove . substr($number, 2);
        }

        // caso onde o numero nao possui o prefixo do estado
        if (strlen($number) == 9 || strlen($number) == 8)
        {
            $number = $defaultPrefix . $number;
        }

        // formata numero caso necessario
        if ($format)
        {
            // formatacao diferente quando for celular ou fone fixo
            $isCelular = ( strlen($number) == 11 );

            if ($isCelular)
            {
                $number = '(' . substr($number, 0, 2) . ')' . substr($number, 2, 5) . '-' . substr($number, 7, 4);
            }
            else
            {
                $number = '(' . substr($number, 0, 2) . ')' . substr($number, 2, 4) . '-' . substr($number, 6, 4);
            }
        }

        return $number;
    }

    /**
     * Return a random brasilian cellphone number
     *
     * @return string the random generate cellphone
     *
     */
    public static function createRandom()
    {
        $number = '';

        for ($i = 0; $i < 11; $i++)
        {
            $number .= rand(0, 9);
        }

        $number[2] = '9';

        return $number;
    }

}
