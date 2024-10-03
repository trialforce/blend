<?php

namespace Validator;

/**
 * Integer validator
 *
 */
class Integer extends \Validator\Validator implements \JsonSerializable
{

    /**
     * Return the value
     *
     * @return int
     */
    public function getValue()
    {
        return intval($this->value);
    }

    /**
     * Define a new value
     *
     * @param string $value
     * @return \Type\Integer
     */
    public function setValue($value, $parse = TRUE)
    {
        if ($parse)
        {
            $value = Integer::onlyNumbers($value);
        }

        $this->value = intval($value);
        return $this;
    }

    /**
     * Add some value
     *
     * @param mixed $value
     *
     * @return Int
     */
    public function add($value)
    {
        if (!$value instanceof Integer)
        {
            $value = Integer::get($value, !is_int($value));
        }

        $this->value = $this->getValue() + $value->getValue();

        return $this;
    }

    /**
     * Subtract value
     *
     * @param mixed $value
     * @return Int
     */
    public function sub($value)
    {
        return $this->add($value * -1);
    }

    public function __toString()
    {
        return (string) $this->getValue();
    }

    public function toHuman()
    {
        return $this->__toString();
    }

    /**
     * Get some string and return only the numbers of that string
     *
     * @param string $value
     * @return int
     */
    public static function onlyNumbers($value)
    {
        return intval(preg_replace("/[^0-9]/", "", $value));
    }

    /**
     * Verifica se a string passada é numérica
     *
     * @param string $value
     * @return boolean
     */
    public static function isNumeric($value)
    {
        //desconsidera valores brasileiros
        $value = trim(str_replace(array(',', '.', 'R$'), '', strtoupper($value)));
        return is_numeric($value);
    }

    /**
     * Static constructor
     *
     * @param type $value
     *
     * @return \Type\Integer
     */
    public static function get($value = null, $column = null)
    {
        return new Integer($value, $column);
    }

    /**
     * Get a value of an integer
     *
     * @param string $value
     * @param boolean $parse
     * @return int
     */
    public static function value($value, $parse = TRUE)
    {
        return Integer::get($value, $parse)->getValue();
    }

    public function toDb()
    {
        return $this->getValue();
    }

    public function validate($value = NULL)
    {
        $error = parent::validate($value);

        if ($this->value)
        {
            if (!\Validator\Validator::isInteger($this->value))
            {
                $error[] = 'Valor deve ser um número inteiro.';
            }
        }

        return $error;
    }

    public function jsonSerialize():mixed
    {
        return $this->toDb();
    }

}
