<?php

namespace Type;

/**
 * Tipe bytes
 */
class Bytes implements \Type\Generic, \JsonSerializable
{

    const PETA = 1125899906842624;
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
    protected $limit = self::PETA;

    public function __construct($value = null, $limit = null)
    {
        $this->setValue($value);

        if ($limit)
        {
            $this->limit = $limit;
        }
    }

    public function __toString()
    {
        $finalValueString = '';
        $value = intval($this->value);

        if ($value < 0)
        {
            $value = $value * -1;
        }

        if ($value > self::PETA && $this->limit >= self::PETA)
        {
            $petas = $this->getValueType($value, self::PETA);
            $finalValueString .= $petas . ' PB';
            $bytesEmPetas = (self::PETA * $petas);
            $value = ($value - $bytesEmPetas);
        }
        else if ($value > self::TERA && $this->limit >= self::TERA)
        {
            $teras = $this->getValueType($value, self::TERA);
            $finalValueString .= $teras . ' TB';
            $bytesEmTeras = (self::TERA * $teras);
            $value = ($value - $bytesEmTeras);
        }
        else if ($value > self::GIGA && $this->limit >= self::GIGA)
        {
            $gigas = $this->getValueType($value, self::GIGA);
            $finalValueString .= $gigas . ' GB';
            $value = ($value - (self::GIGA * $gigas));
        }
        else if ($value > self::MEGA && $this->limit >= self::MEGA)
        {
            $megas = $this->getValueType($value, self::MEGA);
            $finalValueString .= $megas . ' MB';
            $value = ($value - (self::MEGA * $megas));
        }
        else if ($value > self::KILO && $this->limit >= self::KILO)
        {
            $kilos = $this->getValueType($value, self::KILO);
            $finalValueString .= $kilos . ' KB';
            $value = ($value - (self::KILO * $kilos));
        }

        if (strlen($finalValueString) == 0)
        {
            return '0 KB';
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
        if (is_numeric($this->value))
        {
            return number_format($this->value, $this->decimals, '.', '');
        }

        return 0;
    }

    public static function get($value)
    {
        return new \Type\Bytes($value);
    }

    public static function value($value)
    {
        return \Type\Bytes::get($value)->getValue();
    }

    public function jsonSerialize() :mixed
    {
        return $this->toDb();
    }

}
