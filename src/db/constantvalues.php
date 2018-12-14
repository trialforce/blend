<?php

namespace Db;

class ConstantValues implements \ArrayAccess, \Iterator, \Countable
{

    private $position = 0;

    public function __construct()
    {
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
     * Return the an array of object with id and value property
     *
     * @return array of \stdClass
     */
    public function getObjectArray()
    {
        $array = $this->getArray();
        $result = [];

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

    public function offsetSet($offset, $value)
    {
        //readonly
    }

    public function offsetUnset($offset)
    {
        //readonly
    }

    public function offsetExists($offset)
    {
        $array = $this->getArray();
        return isset($array[$offset]);
    }

    public function offsetGet($offset)
    {
        $array = $this->getArray();

        return isset($array[$offset]) ? $array[$offset] : null;
    }

    public function count()
    {
        return count($this->getArray());
    }

    public function current()
    {
        $array = $this->getArray();
        return $array[$this->position];
    }

    public function key()
    {
        return $this->position;
    }

    public function next()
    {
        ++$this->position;
    }

    public function rewind()
    {
        $this->position = 0;
    }

    public function valid()
    {
        $array = $this->getArray();
        return isset($array[$this->position]);
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
