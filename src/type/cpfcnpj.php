<?php

namespace Type;

/**
 * Type Cpf/Cnpj
 */
class CpfCnpj implements \Type\Generic
{

    protected $value;

    public function __construct($value = NULL)
    {
        $this->setValue($value);
    }

    public function __toString()
    {
        return \Validator\CnpjCpf::mask($this->value);
    }

    public function toHuman()
    {
        return \Validator\CnpjCpf::mask($this->value);
    }

    public function getValue()
    {
        return \Validator\CnpjCpf::unmask($this->value);
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
        return \Validator\CnpjCpf::unmask($this->value);
    }

    public static function get($value = null)
    {
        return new \Type\CpfCnpj($value);
    }

    public static function value($value = null)
    {
        return \Type\CpfCnpj::get($value)->getValue();
    }

}
