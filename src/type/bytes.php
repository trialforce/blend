<?php

namespace Type;

/**
 * Tipe bytes
 */
class Bytes implements \Type\Generic, \JsonSerializable
{

    const TERA = 1099511627776;
    const GIGA = 1073741824;
    const MEGA = 1048576;
    const KILO = 1024;

    /**
     *
     * @var type
     */
    protected $value;

    /**
     * Decimals
     *
     * @var float
     */
    protected $decimals = 0;

    public function __construct($value = null)
    {
        $this->setValue($value);
    }

    public function __toString()
    {
        if (strlen($this->value) == 0)
        {
            return '0 KB';
        }

        $finalValueString = '';
        $value = intval($this->value);

        if ($value > self::TERA)
        {
            $teras = $this->getValueType($value, self::TERA);
            $finalValueString .= $teras . ' TB';
            $bytesEmTeras = (self::TERA * $teras);
            $value = ($value - $bytesEmTeras);
        }
        else if ($value > self::GIGA)
        {
            $gigas = $this->getValueType($value, self::GIGA);
            $finalValueString .= $gigas . ' GB';
            $value = ($value - (self::GIGA * $gigas));
        }
        else if ($value > self::MEGA)
        {
            $megas = $this->getValueType($value, self::MEGA);
            $finalValueString .= $megas . ' MB';
            $value = ($value - (self::MEGA * $megas));
        }
        else if ($value > self::KILO)
        {
            $kilos = $this->getValueType($value, self::KILO);
            $finalValueString .= $kilos . ' KB';
            $value = ($value - (self::KILO * $kilos));
        }

        return $finalValueString;
    }

    public function getValueType($value, $type)
    {
        if ($value > $type)
        {
            $newValue = $value / $type;
            $newValue = self::numberFormatPrecision($newValue, 2, ".");
            $newValue = number_format($newValue, 2, ",", "");
            return intval($newValue);
        }

        return 0;
    }

    public static function numberFormatPrecision($number, $precision = 2, $separator = '.')
    {
        $numberParts = explode($separator, $number);
        $response = $numberParts[0];
        if (count($numberParts) > 1)
        {
            $response .= $separator;
            $response .= substr($numberParts[1], 0, $precision);
        }
        return $response;
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
        if ($value instanceof \Type\Bytes)
        {
            $value = $value->getValue();
        }

        //to avoid warning in toDb and toString
        $value = $value ? $value : 0;
        $this->value = $value;
        return $this;
    }

    public function toDb()
    {
        return number_format($this->value, $this->decimals, '.', '');
    }

    public static function get($value)
    {
        return new \Type\Bytes($value);
    }

    public static function value($value)
    {
        return \Type\Bytes::get($value)->getValue();
    }

    public function jsonSerialize()
    {
        return $this->toDb();
    }

}
