<?php

namespace Type;

/**
 * Tipo boolean /valu
 */
class Money implements \Type\Generic
{

    /**
     *
     * @var type
     */
    protected $value;

    /**
     * Decimals
     *
     * @var int
     */
    protected $decimals = 2;

    public function __construct($value)
    {
        $this->setValue($value);
    }

    public function __toString()
    {
        if (strlen($this->value) == 0)
        {
            return '';
        }

        if ($this->value < 0)
        {
            return '- <small>R$</small> ' . number_format(abs($this->value), $this->decimals, ',', '.');
        }
        else
        {
            return '<small>R$</small> ' . number_format($this->value, $this->decimals, ',', '.');
        }
    }

    public function toHuman()
    {
        return $this->__toString();
    }

    public function getDecimals()
    {
        return $this->decimals;
    }

    public function setDecimals($decimals)
    {
        $this->decimals = $decimals;
        return $this;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function getFormatedValue()
    {
        return number_format($this->value, $this->decimals, ',', '.');
    }

    public function setValue($value)
    {
        if ($value instanceof \Type\Money)
        {
            $value = $value->getValue();
        }

        //remove reais
        $value = str_replace('R$', '', $value);

        //support brazilian format
        if (stripos($value, ','))
        {
            $value = str_replace('.', '', $value);
            $value = str_replace(',', '.', $value);
        }

        //to avoid warning in toDb and toString
        $value = $value ? $value : 0;
        $this->value = floatval($value);
        return $this;
    }

    public function toDb()
    {
        return number_format($this->value, $this->decimals, '.', '');
    }

    public function sum($amount)
    {
        if (!$amount instanceof \Type\Money)
        {
            $amount = \Type\Money::get($amount);
        }

        $this->setValue($this->getValue() + $amount->getValue());

        return $this;
    }

    public static function get($value)
    {
        return new \Type\Money($value);
    }

    public static function value($value)
    {
        return \Type\Money::get($value)->getValue();
    }

    public static function valorPorExtenso($valor = 0, $centenaomplemento = true)
    {
        $singular = array("centavo", "real", "mil", "milhão", "bilhão", "trilhão", "quatrilhão");
        $plural = array("centavos", "reais", "mil", "milhões", "bilhões", "trilhões", "quatrilhões");

        $centena = array("", "cem", "duzentos", "trezentos", "quatrocentos", "quinhentos", "seiscentos", "setecentos", "oitocentos", "novecentos");
        $dezena = array("", "dez", "vinte", "trinta", "quarenta", "cinquenta", "sessenta", "setenta", "oitenta", "noventa");
        $dezena10 = array("dez", "onze", "doze", "treze", "quatorze", "quinze", "dezesseis", "dezesete", "dezoito", "dezenove");
        $unidade = array("", "um", "dois", "três", "quatro", "cinco", "seis", "sete", "oito", "nove");

        $z = 0;
        $rt = NULL;

        $valor = number_format($valor, 2, ".", ".");
        $inteiro = explode(".", $valor);

        for ($i = 0; $i < count($inteiro); $i++)
        {
            for ($ii = strlen($inteiro[$i]); $ii < 3; $ii++)
            {
                $inteiro[$i] = "0" . $inteiro[$i];
            }
        }

        // $fim identifica onde que deve se dar junção de centenas por "e" ou por "," ;)
        $fim = count($inteiro) - ($inteiro[count($inteiro) - 1] > 0 ? 1 : 2);

        for ($i = 0; $i < count($inteiro); $i++)
        {
            $valor = $inteiro[$i];
            $rc = (($valor > 100) && ($valor < 200)) ? "cento" : $centena[$valor[0]];
            $rd = ($valor[1] < 2) ? "" : $dezena[$valor[1]];
            $ru = ($valor > 0) ? (($valor[1] == 1) ? $dezena10[$valor[2]] : $unidade[$valor[2]]) : "";

            $r = $rc . (($rc && ($rd || $ru)) ? " e " : "") . $rd . (($rd && $ru) ? " e " : "") . $ru;
            $t = count($inteiro) - 1 - $i;

            if ($centenaomplemento == TRUE)
            {
                $r .= $r ? " " . ($valor > 1 ? $plural[$t] : $singular[$t]) : "";

                if ($valor == "000")
                {
                    $z++;
                }
                elseif ($z > 0)
                {
                    $z--;
                }

                if (($t == 1) && ($z > 0) && ($inteiro[0] > 0))
                {
                    $r .= (($z > 1) ? " de " : "") . $plural[$t];
                }
            }

            if ($r)
            {
                $rt = $rt . ((($i > 0) && ($i <= $fim) && ($inteiro[0] > 0) && ($z < 1)) ? ( ($i < $fim) ? ", " : " e ") : " ") . $r;
            }
        }

        return trim($rt ? $rt : "zero");
    }

}
