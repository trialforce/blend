<?php

namespace Type;

/**
 * Type float/decimal
 */
class Decimal implements \Type\Generic, \JsonSerializable
{

    /**
     * Value
     * @var float
     */
    protected $value;

    /**
     * Decimals
     *
     * @var int
     */
    protected $decimals = 2;

    /**
     *
     * @var string
     */
    protected $preffix = '';

    /**
     *
     * @var string
     */
    protected $suffix = '';

    public function __construct($value = NULL, $decimals = 2)
    {
        if ($value instanceof \Type\Generic)
        {
            $value = $value->toDb();
        }

        $this->setValue($value);

        if (!($decimals || $decimals === 0))
        {
            $decimals = 2;
        }

        $this->setDecimals($decimals);
    }

    /**
     * Return decimals
     *
     * @return int
     */
    public function getDecimals()
    {
        return $this->decimals;
    }

    /**
     * Define decimals
     *
     * @param string $decimals
     * @return \Type\Decimal
     */
    public function setDecimals($decimals)
    {
        $this->decimals = $decimals;
        return $this;
    }

    public function getPreffix()
    {
        return $this->preffix;
    }

    public function getSuffix()
    {
        return $this->suffix;
    }

    public function setPreffix($preffix)
    {
        $this->preffix = $preffix;
        return $this;
    }

    public function setSuffix($suffix)
    {
        $this->suffix = $suffix;
        return $this;
    }

    public function __toString()
    {
        if (strlen($this->value) == 0)
        {
            return '';
        }

        return $this->preffix . number_format($this->value, $this->decimals, ',', '.') . $this->suffix;
    }

    public function toHuman()
    {
        return $this->__toString();
    }

    public function getValue()
    {
        return $this->value;
    }

    /**
     * Return the int part of decimal
     *
     * @return int
     */
    public function getIntPart()
    {
        $explode = explode('.', $this->value);

        return intval($explode[0]);
    }

    public function setValue($value)
    {
        if ($value instanceof \Type\Generic)
        {
            $value = $value->getValue();
        }

        $value = self::treatValue($value);
        $this->value = floatval($value);

        return $this;
    }

    /**
     * Treat value to support brazilian format
     *
     * @param string $value
     * @return string
     */
    public static function treatValue($value)
    {
        //remove reais
        $value = str_replace('R$', '', $value.'');

        //support brazilian format
        if (stripos($value, ','))
        {
            $value = str_replace('.', '', $value);
            $value = str_replace(',', '.', $value);
        }

        return $value;
    }

    public function toDb()
    {
        return number_format($this->value, $this->decimals, '.', '');
    }

    public function jsonSerialize():mixed
    {
        return $this->toDb();
    }

    public function sum($amount)
    {
        if (!$amount instanceof \Type\Decimal)
        {
            $amount = \Type\Decimal::get($amount);
        }

        $this->setValue($this->getValue() + $amount->getValue());

        return $this;
    }

    public static function get($value = null)
    {
        return new \Type\Decimal($value . '');
    }

    public static function value($value)
    {
        return \Type\Decimal::get($value)->getValue();
    }

    /**
     * Compare with 5 decimals
     *
     * @param mixed $otherFloat
     * @return boolean
     */
    public function equals($otherFloat)
    {
        if (!$otherFloat instanceof \Type\Decimal)
        {
            $otherFloat = new \Type\Decimal($otherFloat);
        }

        $valueA = $this->toDb();
        $valueB = $otherFloat->toDb();
        $epsilon = 0.00001;

        return abs($valueA - $valueB) < $epsilon;
    }

}
