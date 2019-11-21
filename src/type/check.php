<?php

namespace Type;

/**
 * Tipo boolean /valu
 */
class Check implements \Type\Generic
{

    protected $value;

    public function __construct($value = null)
    {
        $this->setValue($value);
    }

    public function __toString()
    {
        return $this->value . '';
    }

    public function getValue()
    {
        return intval($this->value);
    }

    public function setValue($value)
    {
        if ($value instanceof Check)
        {
            $value = $value->getValue();
        }

        $this->value = $value;

        return $this;
    }

    public function toDb()
    {
        if ($this->value <= 0)
        {
            $conn = \Db\Conn::getConnInfo();

            //alternativa para o mysql
            if ($conn->getType() == \Db\ConnInfo::TYPE_MYSQL)
            {
                return '0.0';
            }

            return 0;
        }

        return 1;
    }

    public function toHuman()
    {
        return $this->value == '1' ? 'Sim' : 'Não';
    }

    public static function get($value)
    {
        return new \Type\Check($value);
    }

    public static function value($value)
    {
        return \Type\Check::get($value)->getValue();
    }

    /**
     * Random bool
     * @param int $limit
     * @return 1 or 0
     */
    public static function rand($limit = 50)
    {
        return rand(0, 100) > $limit ? 1 : 0;
    }

}
