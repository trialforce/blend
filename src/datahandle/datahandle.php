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
     * @param SimpleXMLElement $data
     */
    public function setData($data)
    {
        //caso for um objeto converte para array
        if (is_object($data) && !$data instanceof SimpleXMLElement)
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

                $this->setVar($var, $value);
            }
        }
        else if ($data instanceof SimpleXMLElement)
        {
            $attributes = $data->attributes();

            foreach ($attributes as $var => $value)
            {
                $this->setVar($var, $value . "");
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
     * @param string $defaultValue
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

}
