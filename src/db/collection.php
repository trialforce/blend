<?php

namespace Db;

/**
 * Simple Collection with ArrayAccess suport
 */
class Collection implements \ArrayAccess, \Iterator, \Countable
{

    /**
     * The data of collection
     * @var type
     */
    protected $data;

    public function __construct($array = NULL)
    {
        if (is_array($array))
        {
            $this->setData($array);
        }
    }

    /**
     * Count
     *
     * @return int
     */
    public function count()
    {
        return count($this->data);
    }

    /**
     * Define all data inside collection
     *
     * @param Array $array
     */
    public function setData(Array $array)
    {
        $this->data = $array;

        return $this;
    }

    /**
     * Return all the data as array
     *
     * @return Array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Add some item to collection
     *
     * @param string $key
     * @param mixed $value
     */
    public function set($key, $value)
    {
        if (is_null($key))
        {
            return $this->data[] = $value;
        }
        else
        {
            return $this->data[$key] = $value;
        }
    }

    /**
     * Set an value by property
     *
     * @param mixed $value
     * @param string $property
     */
    public function setByProperty($value, $property)
    {
        return $this->set($value->$property, $value);
    }

    /**
     * Add a data with index
     *
     * @param mixed $value
     */
    public function add($value)
    {
        $this->set(NULL, $value);
    }

    /**
     * Get a item from data by key
     *
     * @param string $key
     */
    public function get($key)
    {
        return $this->data[$key];
    }

    /**
     * Remove data by key
     *
     * @param string $key
     */
    public function remove($key)
    {
        unset($this->data[$key]);
    }

    /**
     * Clear all data inside collection
     */
    public function clear()
    {
        $this->data = array();
    }

    /**
     * Array set
     *
     * For implements \ArrayAccess.
     *
     * @param int $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {
        if (is_null($offset))
        {
            $this->data[] = $value;
        }
        else
        {
            $this->data[$offset] = $value;
        }
    }

    /**
     * Array offset exists.
     *
     * For implements \ArrayAccess.
     *
     * @param int $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->data[$offset]);
    }

    /**
     * Array offset unset
     *
     * For implements \ArrayAccess.
     *
     * @param bool $offset
     */
    public function offsetUnset($offset)
    {
        unset($this->data[$offset]);
    }

    /**
     * Array offset get
     *
     * For implements \ArrayAccess.
     *
     * @param type $offset
     * @return type
     */
    public function offsetGet($offset)
    {
        return isset($this->data[$offset]) ? $this->data[$offset] : null;
    }

    /**
     * Iterator rewind
     *
     * For implements \Iterator (foreach)
     *
     * @return type
     */
    public function rewind()
    {
        return reset($this->data);
    }

    /**
     * Iterator current
     *
     * For implements \Iterator (foreach)
     *
     * @return type
     */
    function current()
    {
        return current($this->data);
    }

    /**
     * Iterator key
     *
     * For implements \Iterator  (foreach)
     *
     * @return bool
     */
    public function key()
    {
        return key($this->data);
    }

    /**
     * Iterador next
     *
     * For implements \Iterator (foreach)
     *
     * @return bool
     */
    public function next()
    {
        return next($this->data);
    }

    /**
     * Iterator valid
     *
     * For implements \Iterator (foreach)
     *
     * @return bool
     */
    public function valid()
    {
        return key($this->data) !== null;
    }

    /**
     * Implements object set
     *
     * @param string $name
     * @param mixed $value
     */
    public function __set($name, $value)
    {
        $this->data[$name] = $value;
    }

    /**
     * Implements object get
     *
     * @param string $name
     *
     * @return mixed
     */
    public function __get($name)
    {
        if (isset($this->data[$name]))
        {
            return $this->data[$name];
        }

        return NULL;
    }

    public function __isset($name)
    {
        return isset($this->data[$name]);
    }

    public function __unset($name)
    {
        unset($this->data[$name]);
    }

}
