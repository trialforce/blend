<?php

namespace Type;

/**
 * Time type
 */
class Vector implements \Type\Generic, \JsonSerializable
{

    protected $type = null;
    protected $separator = ',';

    public function __construct($value = null, $type = null, $separator = ',')
    {
        $this->setSeparator($separator ? $separator : ',');
        $this->setType($type);
        $this->setValue($value);
    }

    public function getType()
    {
        return $this->type;
    }

    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    public function getSeparator()
    {
        return $this->separator;
    }

    public function setSeparator($separator)
    {
        $this->separator = $separator;
        return $this;
    }

    public function __toString()
    {
        return $this->getValue();
    }

    public function getValue()
    {
        $value = $this->value;

        if (is_array($value))
        {
            $value = implode($this->separator, $value);
        }

        return $value . '';
    }

    public function setValue($value)
    {
        if (is_string($value))
        {
            $value = explode($this->separator, $value);
        }

        $this->value = $value;
    }

    public function toDb()
    {
        return $this->getValue();
    }

    public function toHuman()
    {
        return $this->getValue();
    }

    public static function get($value = null, $type = null, $separator = ',')
    {
        return new \Type\Vector($value, $type, $separator);
    }

    public static function value($value = null, $type = null, $separator = ',')
    {
        return \Type\Vector::get($value, $type, $separator)->value();
    }

    public function jsonSerialize():mixed
    {
        return $this->toDb();
    }

}
