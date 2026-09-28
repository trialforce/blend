<?php

namespace DataHandle;

class DataHandle
{

    /**
     * Variável estática para singleton
     *
     * @var array
     */
    protected static $dataHandle = array();

    public function __construct($data = NULL)
    {
        $this->setData($data);
    }

    /**
     * Retorna instance prévia usando singleton
     *
     * @return DataHandle
     */
    public static function getInstance()
    {
        $class = get_called_class();

        if (!isset(self::$dataHandle[$class]))
        {
            self::$dataHandle[$class] = new $class();
        }

        return self::$dataHandle[$class];
    }

    /**
     * Define os dados suporte array e xml
     *
     * @param \SimpleXMLElement $data
     */
    public function setData($data)
    {
        //caso for um objeto converte para array
        if (is_object($data) && !$data instanceof \SimpleXMLElement)
        {
            $data = (array) $data;
        }

        if (is_array($data))
        {
            foreach ($data as $var => $value)
            {
                //remove script
                if (is_string($value))
                {
                    $value = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $value);
                }

                $this->$var = $value;
            }
        }
        else if ($data instanceof \SimpleXMLElement)
        {
            $attributes = $data->attributes();

            foreach ($attributes as $var => $value)
            {
                $this->$var = $value.'';
            }
        }
    }

    /**
     * Set a variable
     *
     * @param string $var
     * @param mixed $value
     */
    public static function set($var, $value)
    {
        $class = get_called_class();
        $instance = $class::getInstance();
        $instance->setVar($var, $value);
    }

    /**
     * Get a variable
     *
     * @param string $var
     * @return mixed
     */
    public static function get($var)
    {
        $class = get_called_class();
        $instance = $class::getInstance();
        return $instance->getVar($var);
    }

    /**
     * Retorna uma variável como string.
     *
     * @param string $var
     * @return string
     * @throws \UnexpectedValueException
     */
    public static function getString($var): string
    {
        $value = static::getValue($var);

        if ($value === null)
        {
            return '';
        }

        if (!is_string($value))
        {
            throw new \UnexpectedValueException('O parâmetro ' . $var . ' deve ser uma string.');
        }

        return $value;
    }

    /**
     * Retorna uma variável como string ou null.
     *
     * @param string $var
     * @return string|null
     * @throws \UnexpectedValueException
     */
    public static function getStringOrNull($var): ?string
    {
        $value = static::getValue($var);

        if ($value === null)
        {
            return null;
        }

        if (!is_string($value))
        {
            throw new \UnexpectedValueException('O parâmetro ' . $var . ' deve ser uma string.');
        }

        return $value;
    }

    /**
     * Retorna uma variável como inteiro.
     *
     * @param string $var
     * @return int
     * @throws \UnexpectedValueException
     */
    public static function getInt($var): int
    {
        $value = static::getValue($var);

        if ($value === null)
        {
            return 0;
        }

        $int = filter_var($value, FILTER_VALIDATE_INT);

        if ($int === false)
        {
            throw new \UnexpectedValueException('O parâmetro ' . $var . ' deve ser um inteiro.');
        }

        return $int;
    }

    /**
     * Retorna uma variável como inteiro ou null.
     *
     * @param string $var
     * @return int|null
     * @throws \UnexpectedValueException
     */
    public static function getIntOrNull($var): ?int
    {
        $value = static::getValue($var);

        if ($value === null)
        {
            return null;
        }

        $int = filter_var($value, FILTER_VALIDATE_INT);

        if ($int === false)
        {
            throw new \UnexpectedValueException('O parâmetro ' . $var . ' deve ser um inteiro.');
        }

        return $int;
    }

    /**
     * Retorna uma variável como float.
     *
     * @param string $var
     * @return float
     * @throws \UnexpectedValueException
     */
    public static function getFloat($var): float
    {
        $value = static::getValue($var);

        if ($value === null)
        {
            return 0.0;
        }

        $float = filter_var($value, FILTER_VALIDATE_FLOAT);

        if ($float === false)
        {
            throw new \UnexpectedValueException('O parâmetro ' . $var . ' deve ser um número decimal.');
        }

        return $float;
    }

    /**
     * Retorna uma variável como float ou null.
     *
     * @param string $var
     * @return float|null
     * @throws \UnexpectedValueException
     */
    public static function getFloatOrNull($var): ?float
    {
        $value = static::getValue($var);

        if ($value === null)
        {
            return null;
        }

        $float = filter_var($value, FILTER_VALIDATE_FLOAT);

        if ($float === false)
        {
            throw new \UnexpectedValueException('O parâmetro ' . $var . ' deve ser um número decimal.');
        }

        return $float;
    }

    /**
     * Verify is some variable exists in datahandle
     *
     * @param string $var
     * @return boolean
     */
    public static function exists($var)
    {
        $class = get_called_class();
        $instance = $class::getInstance();
        return isset($instance->$var);
    }

    /**
     * Get a var, if not return default passed value and set it in object
     *
     * @param string $var
     * @param mixed $defaultValue
     * @return mixed
     */
    public static function getDefault($var, $defaultValue)
    {
        $var = str_replace('.', '_', $var);
        $value = self::get($var);

        if (!$value || (is_string($value) && mb_strlen($value) === 0))
        {
            self::set($var, $defaultValue);
            $value = $defaultValue;
        }

        return $value;
    }

    /**
     * Define variável
     *
     * @param string $var
     * @param mixed $value
     */
    public function setVar($var, $value)
    {
        if ($var)
        {
            $var = str_replace('.', '_', $var);
            $this->$var = $value;
        }
    }

    /**
     * Return the content of variable
     *
     * @param string $var
     * @return mixed
     */
    public function getVar($var)
    {
        if (isset($this->$var))
        {
            return $this->$var;
        }

        return null;
    }

    /**
     * Remove passed variable
     *
     * @param string $var
     * @return $this
     */
    public function remove($var)
    {
        if (isset($this->$var))
        {
            unset($this->$var);
        }

        return $this;
    }

    /**
     * Retorna o valor e rejeita arrays.
     *
     * @param string $var
     * @return mixed
     * @throws \UnexpectedValueException
     */
    private static function getValue($var)
    {
        $value = static::get($var);

        if (is_array($value))
        {
            throw new \UnexpectedValueException('O parâmetro ' . $var . ' não pode ser um array.');
        }

        return $value;
    }

}
