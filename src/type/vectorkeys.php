<?php

namespace Type;

/**
 * Type of keys of Array, used with Checkbox
 */
class VectorKeys implements \Type\Generic
{

    protected $value;

    public function __construct($value = NULL, $keys = true)
    {
        if (is_array($value))
        {
            if ($keys)
            {
                $value = array_keys(array_filter($value));
            }
        }
        else
        {
            $value = explode(',', $value);
        }

        $this->setValue($value);
    }

    public function __toString()
    {
        return implode(',', $this->value);
    }

    public function toHuman()
    {
        return $this->__toString();
    }

    public function getValue()
    {
        return $this->value;
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

    public function toDb()
    {
        return implode(',', $this->value);
    }

    public static function get($value)
    {
        return new \Type\VectorKeys($value);
    }

    public static function value($value)
    {
        return \Type\VectorKeys::get($value)->getValue();
    }

}