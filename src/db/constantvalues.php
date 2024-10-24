<?php

namespace Db;

/**
 * A list of constante values, is iterable, countable, you can access
 * like an array and work like a column format trough \Type\Generic
 */
class ConstantValues implements \ArrayAccess, \Iterator, \Countable, \Type\Generic
{

    /**
     * Used by \Type\Generic
     * @var int
     */
    private $value = 0;

    /**
     * Used to array access
     * @var int
     */
    private $position = 0;

    public function __construct($value = NULL)
    {
        $this->setValue($value);
        $this->position = 0;
    }

    /**
     * Return an array with value -> description
     * @return array
     */
    public function getArray()
    {
        $reflectionClass = new \ReflectionClass($this);
        $variables = array_flip($reflectionClass->getConstants());

        foreach ($variables as $key => $value)
        {
            $label = str_replace('_', ' ', $value);
            $label = ucwords(strtolower($label));
            $variables[$key] = $label;
        }

        return $variables;
    }

    /**
     * Return an array with value -> description
     * But description is converted to camelCase
     * @return int[]|string[]
     */
    public function getArrayCamelCase()
    {
        $reflectionClass = new \ReflectionClass($this);
        $variables = array_flip($reflectionClass->getConstants());

        foreach ($variables as $key => $value)
        {
            $value = strtolower($value);
            $label =  lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $value))));
            $variables[$key] = $label;
        }

        return $variables;
    }

    /**
     * Return the an array of object with id and value property
     *
     * @return array of \stdClass
     */
    public function getObjectArray()
    {
        $array = $this->getArray();
        $result = array();

        if (is_array($array))
        {
            foreach ($array as $key => $item)
            {
                $stdClass = new \stdClass();
                $stdClass->id = $key;
                $stdClass->value = $item;

                $result[] = $stdClass;
            }
        }

        return $result;
    }

    /**
     * Return key description
     *
     * @param int $key
     * @return string
     */
    public function getKeyDescription($key)
    {
        $array = $this->getArray();

        if (isset($array[$key]))
        {
            return $array[$key];
        }

        return NULL;
    }

    public function offsetSet($offset, $value) :void
    {
        //readonly
    }

    public function offsetUnset($offset) : void
    {
        //readonly
    }

    public function offsetExists($offset) :bool
    {
        $array = $this->getArray();
        return isset($array[$offset]);
    }

    public function offsetGet($offset) :mixed
    {
        $array = $this->getArray();

        return isset($array[$offset]) ? $array[$offset] : null;
    }

    public function count():int
    {
        return count($this->getArray());
    }

    public function current() : mixed
    {
        $array = $this->getArray();
        return $array[$this->position];
    }

    public function key() : mixed
    {
        return $this->position;
    }

    public function next() :void
    {
        ++$this->position;
    }

    public function rewind() : void
    {
        $this->position = 0;
    }

    public function valid() : bool
    {
        $array = $this->getArray();
        return isset($array[$this->position]);
    }

    public function getValue()
    {
        return $this->value;
    }

    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    public function toDb()
    {
        return $this->value;
    }

    public function toHuman()
    {
        $array = $this->getArray();

        if (isset($array[$this->value]))
        {
            return $array[$this->value];
        }
    }

    public function __toString()
    {
        return $this->toHuman().'';
    }

    /**
     * Return an instance of this constant values
     *
     * @return \Db\ConstantValues
     */
    public static function getInstance()
    {
        $className = get_called_class();

        return new $className();
    }

    /**
     * Return an instance of this constant values
     *
     * @return \Db\ConstantValues
     */
    public static function get($value)
    {
        $className = get_called_class();
        return new $className($value . '');
    }

    public static function value($value)
    {
        $className = get_called_class();
        return $className::get($value)->getValue();
    }

    /**
     * Return simple array of values
     *
     * @return array
     */
    public static function toArray()
    {
        $className = get_called_class();
        return $className::getInstance()->getArray();
    }

}
