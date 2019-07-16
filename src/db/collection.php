<?php

namespace Db;

/**
 * Simple Collection with ArrayAccess suport
 */
class Collection implements \ArrayAccess, \Iterator, \Countable, \JsonSerializable
{

    /**
     * The data of collection
     * @var type
     */
    protected $data = array();

    public function __construct($data = NULL)
    {
        if (is_array($data))
        {
            $this->setData($data);
        }
    }

    /**
     *
     * @return \Db\Collection
     */
    public static function create($data)
    {
        return new \Db\Collection($data);
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
     * Compatibility
     *
     * @return int
     */
    public function length()
    {
        return $this->count();
    }

    /**
     * Define all data inside collection
     *
     * @param Array $array
     */
    public function setData($array)
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
     * Return the first item of array
     *
     * @return mixes
     */
    public function first()
    {
        return array_values($this->data)[0];
    }

    public function filter($function)
    {
        $new = array();

        foreach ($this->data as $idx => $item)
        {
            if ($function($item))
            {
                $new[] = $item;
            }
        }

        $this->data = $new;

        return $this;
    }

    /**
     * Order the colletion by some property
     *
     * @param string $orderBy order by
     * @param string $orderWay order way
     * @return $this
     */
    public function orderBy($orderBy, $orderWay = NULL)
    {
        usort($this->data, function($a, $b) use(&$orderBy)
        {
            $valueA = '';
            $valueB = '';

            if (is_array($a))
            {
                $valueA = $a[$orderBy];
                $valueB = $b[$orderBy];
            }
            else if (is_object($a))
            {
                $methodA = 'get' . $orderBy;
                $methodB = 'get' . $orderBy;

                if (method_exists($a, $methodA))
                {
                    $valueA = $a->$methodA();
                    $valueB = $b->$methodB();
                }
                else
                {
                    $valueA = $a->$orderBy;
                    $valueB = $b->$orderBy;
                }
            }

            return strcmp($valueA, $valueB);
        });

        //apply the order
        if (strtolower($orderWay) != 'desc')
        {
            $this->data = array_reverse($this->data);
        }

        return $this;
    }

    /**
     * Apply a limit to the collection
     * @param int $limit limit
     * @param int $offset offset
     * @return $this
     */
    public function limit($limit, $offset = NULL)
    {
        $this->data = array_slice($this->data, $offset, $limit);

        return $this;
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
            $this->data[] = $value;
        }
        else
        {
            $this->data[$key] = $value;
        }

        return $this;
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
        //need interface
        if ($value instanceof \Db\ConstantValues)
        {
            $value = $value->getArray();
        }
        else if ($value instanceof \Db\Collection)
        {
            $value = $value->getData();
        }

        if (is_array($value))
        {
            $this->data = $this->data + $value;
        }
        else
        {
            $this->set(NULL, $value);
        }

        return $this;
    }

    /**
     * Push a data to array, same effect as add
     *
     * @param mixed $value
     */
    public function push($value)
    {
        return $this->add($value);
    }

    /**
     * Index an sort the data by a property
     *
     * @param string $property
     * @return \Db\Collection
     */
    public function indexByProperty($property)
    {
        $result = NULL;
        $array = $this->getData();

        if (is_array($array))
        {
            foreach ($array as $item)
            {
                //array
                if (is_array($item))
                {
                    $index = $item[$property];
                }
                else if (is_object($item))
                {
                    //model
                    if ($item instanceof \Db\Model)
                    {
                        $index = $item->getValue($property);
                    }
                    //simple object
                    else
                    {
                        $index = $item->$property;
                    }
                }

                $result[$index] = $item;
            }
        }

        if ($result)
        {
            ksort($result);
        }

        $this->setData($result);

        return $this;
    }

    /**
     * Create a simple array with key/value, using passed parameter
     *
     * @param string $key the key of new array
     * @param string $value the value of new array
     * @return array an key/value array
     */
    public function toKeyValue($key, $value)
    {
        $result = NULL;
        $array = $this->getData();

        if (is_array($array))
        {
            foreach ($array as $item)
            {
                //array
                if (is_array($item))
                {
                    $vKey = $item[$key];
                    $vValue = $item[$vValue];
                }
                else if (is_object($item))
                {
                    //model
                    if ($item instanceof \Db\Model)
                    {
                        $vKey = $item->getValue($key);
                        $vValue = $item->getValue($value);
                    }
                    //simple object
                    else
                    {
                        $vKey = $item->$key;
                        $vValue = $item->$value;
                    }
                }

                $result[$vKey] = $vValue;
            }
        }

        if ($result)
        {
            ksort($result);
        }

        $this->setData($result);

        return $this;
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
        if (!is_array($this->data))
        {
            $this->data = array();
            return null;
        }

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
        if (!is_array($this->data))
        {
            return null;
        }

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
        if (!is_array($this->data))
        {
            return null;
        }

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

    public function __toString()
    {
        return print_r($this->data, TRUE);
    }

    public function jsonSerialize()
    {
        return $this->data;
    }

    public function toJson()
    {
        return json_encode($this);
    }

}
