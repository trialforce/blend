<?php

namespace Db;

/**
 * A Collection with focus to deal with \Db\Model with ArrayAccess suport
 *
 * @template T
 */
class Collection implements \ArrayAccess, \Iterator, \Countable, \JsonSerializable
{

    /**
     * The data of collection
     * @var array<T>
     */
    protected $data = array();

    public function __construct($data = NULL)
    {
        if ($data)
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
    public function count() : int
    {
        if (!is_array($this->data))
        {
            return 0;
        }

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
     * Verify if collection is empty
     */
    public function isEmpty()
    {
        return $this->count() == 0;
    }

    /**
     * Define all data inside collection
     *
     * @param mixed $array
     */
    public function setData($array)
    {
        //need interface
        if ($array instanceof \Db\ConstantValues)
        {
            $array = $array->getArray();
        }
        else if ($array instanceof \Db\Collection)
        {
            $array = $array->getData();
        }

        $this->data = $array;

        return $this;
    }

    /**
     * Return all the data as array
     *
     * @return array<T>
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Return all the data as array
     * Alias to getdata
     *
     * @return array<T>
     */
    public function toArray()
    {
        return $this->data;
    }

    /**
     * Return the first item of array
     *
     * @return T|null
     */
    public function first()
    {
        $result = array_values($this->data);

        if (isset($result[0]))
        {
            return $result[0];
        }

        return null;
    }

    /**
     * Get the last result of the Collection
     * @return T|null
     */
    public function last()
    {
        $result = array_values($this->data);
        $last = count($result) - 1;

        if (isset($result[$last]))
        {
            return $result[$last];
        }

        return null;
    }

    public function filter($function)
    {
        $new = array();

        foreach ($this->data as $item)
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
                $valueA = self::getPropertyFromItem($a, $orderBy);
                $valueB = self::getPropertyFromItem($b, $orderBy);
            }

            //add suporte for pt-br numbers
            if (is_numeric(str_replace(',', '.', $valueA)) && is_numeric(str_replace(',', '.', $valueB)))
            {
                return floatval($valueA) - floatval($valueB);
            }
            else
            {
                return strcmp($valueA, $valueB);
            }
        });

        //apply the order
        if (strtolower($orderWay) == 'desc')
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
     * Make an aggregation passing its method and property
     * sum, max, min, avg, count
     *
     * @param string $method method
     * @param string $property property
     * @return int
     */
    public function aggr($method, $property)
    {
        $method = trim(strtolower($method));

        if ($method == 'sum')
        {
            return $this->sum($property);
        }
        else if ($method == 'max')
        {
            return $this->max($property);
        }
        else if ($method == 'min')
        {
            return $this->min($property);
        }
        else if ($method == 'avg')
        {
            return $this->avg($property);
        }
        else if ($method == 'count')
        {
            return $this->count();
        }
        else if ($method == 'distinct')
        {
            return $this->countDistinct($property);
        }

        return 0;
    }

    /**
     * Make a sum of an property
     *
     * @param string $property the property to sum
     * @return float the total of the sum
     */
    public function sum($property)
    {
        $total = 0;

        foreach ($this->data as  $item)
        {
            $info = self::getPropertyFromItem($item, $property);
            $regexpResult = [];
            preg_match('/([0-9]{1,}):([0-9]{1,}):?([0-9]{1,})?/', $info, $regexpResult);

            if (count($regexpResult) > 0)
            {
                $total += \Type\Time::get($info)->toDecimal()->toDb();
            }
            else
            {

                $total += \Type\Decimal::get($info)->toDb();
            }
        }

        return $total;
    }

    /**
     * Make the average of a property
     *
     * @param string $property
     * @return float
     */
    public function avg($property)
    {
        if ($this->count() == 0)
        {
            return 0;
        }

        $total = 0;

        foreach ($this->data as $item)
        {
            $total += \Type\Decimal::get(self::getPropertyFromItem($item, $property))->toDb();
        }

        return $total / $this->count();
    }

    /**
     * Return the minimum of property
     *
     * @param string $property
     * @return int
     */
    public function min($property)
    {
        $min = 0;

        foreach ($this->data as $item)
        {
            $value = self::getPropertyFromItem($item, $property);

            if ($value < $min)
            {
                $min = $value;
            }
        }

        return $min;
    }

    /**
     * Return the maximum of property
     *
     * @param string $property
     * @return int
     */
    public function max($property)
    {
        $min = 0;

        foreach ($this->data as $item)
        {
            $value = self::getPropertyFromItem($item, $property);

            if ($value > $min)
            {
                $min = $value;
            }
        }

        return $min;
    }

    /**
     * Count distinct values from an specify property
     *
     * @param string $property the object property
     * @return int the count result
     */
    public function countDistinct($property)
    {
        $count = [];

        foreach ($this->data as $item)
        {
            $value = self::getPropertyFromItem($item, $property);
            $count[$value] = 1;
        }

        return count($count);
    }

    /**
     * Add some item to collection
     *
     * @param string $key
     * @param mixed $value
     */
    public function set($key, $value)
    {
        //avoid crappy error
        if (!is_array($this->data))
        {
            $this->data = array();
        }

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
        //don't add if is null
        if (is_null($value))
        {
            return $this;
        }
        //need interface
        else if ($value instanceof \Db\ConstantValues)
        {
            $value = $value->getArray();
        }
        else if ($value instanceof \Db\Collection)
        {
            $value = $value->getData();
        }

        if (is_array($value))
        {
            $this->data = array_merge($this->data, $value);
        }
        else
        {
            $this->set(null, $value);
        }

        return $this;
    }

    /**
     * Index this Collection by property and add distinct values from 'other' param.
     *
     * @param string $property the property to verify the unique/distinct
     * @param mixed $other the object/model
     */
    public function addDistinct($property, $other)
    {
        $this->indexByProperty($property);

        if ($other instanceof \Db\Collection || is_array($other))
        {
            foreach ($other as $item)
            {
                $this->addDistinctExecute($property, $item);
            }
        }
        else
        {
            $this->addDistinctExecute($property, $other);
        }

        return $this;
    }

    /**
     * Add single item to collection if its property is distinct.
     *
     * @param string $property
     * @param T $item
     */
    private function addDistinctExecute($property, $item)
    {
        $index = self::getPropertyFromItem($item, $property);

        if (!isset($this->data[$index]) || !$this->get($index))
        {
            $this->add($item);
        }
    }

    /**
     * Push a data to array, same effect as add
     *
     * @param T $value
     */
    public function push($value)
    {
        return $this->add($value);
    }

    /**
     * Get a property from an item
     *
     * @param T $item
     * @param string $property
     * @return string
     */
    public static function getPropertyFromItem($item, $property)
    {
        $result = null;

        if (is_array($item))
        {
            $result = $item[$property] ?? null;
        }
        else if (is_object($item))
        {
            //model
            if ($item instanceof \Db\Model)
            {
                $result = $item->getValueDb($property);
            }
            //simple object
            else
            {
                $result = $item->$property ?? 0;
            }
        }

        if ($result instanceof \Type\Generic)
        {
            $result = $result->toDb();
        }

        return $result;
    }

    /**
     * Index an sort the data by a property
     *
     * @param string $property
     * @return $this
     */
    public function indexByProperty($property)
    {
        $result = array();
        $array = $this->getData();

        if (is_array($array))
        {
            foreach ($array as $item)
            {
                $index = self::getPropertyFromItem($item, $property);
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
     * @return $this
     */
    public function toKeyValue($key, $value)
    {
        $result = array();
        $array = $this->getData();

        if (is_array($array))
        {
            foreach ($array as $item)
            {
                $vValue = null;
                $vKey = null;

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
     * Limit the collection to a simple array with the property passed
     *
     * @param string $property
     * @return $this
     */
    public function getValues($property)
    {
        $result = array();
        $array = $this->getData();

        if (is_array($array))
        {
            foreach ($array as $item)
            {
                $vKey = null;

                //array
                if (is_array($item))
                {
                    $vKey = $item[$property];
                }
                else if (is_object($item))
                {
                    //model
                    if ($item instanceof \Db\Model)
                    {
                        $vKey = $item->getValue($property);
                    }
                    //simple object
                    else
                    {
                        $vKey = $item->$property;
                    }
                }

                $result[] = $vKey;
            }
        }

        $this->setData($result);

        return $this;
    }

    public function where($columnName, $param = NULL, $value = NULL)
    {
        $data = $this->getData();
        $result = [];

        foreach ($data as $item)
        {
            $myValue = self::getPropertyFromItem($item, $columnName);

            if ($param == '=' && $value == $myValue)
            {
                $result[] = $item;
            }
            else if ($param == '!=' && $value != $myValue)
            {
                $result[] = $item;
            }
            else if ($param == '<>' && $value != $myValue)
            {
                $result[] = $item;
            }
            else if ($param == '<=' && $myValue <= $value)
            {
                $result[] = $item;
            }
            else if ($param == '<' && $myValue < $value)
            {
                $result[] = $item;
            }
            else if ($param == '>=' && $myValue >= $value)
            {
                $result[] = $item;
            }
            else if ($param == '>' && $myValue > $value)
            {
                $result[] = $item;
            }
            else if ($param == 'like' && $myValue)
            {
                //simulate like in database
                if (\Type\Text::get($myValue)->like($value))
                {
                    $result[] = $item;
                }
            }
        }

        $this->setData($result);
        return $this;
    }

    public function whereIf($columnName, $param = NULL, $value = NULL)
    {
        if ($value || $value === 0 || $value === '0')
        {
            return $this->where($columnName, $param, $value);
        }

        return $this;
    }

    public function and($columnName, $param = NULL, $value = NULL)
    {
        return $this->where($columnName, $param, $value);
    }

    public function andIf($columnName, $param = NULL, $value = NULL)
    {
        return $this->whereIf($columnName, $param, $value);
    }

    /**
     * Get a item from data by key
     *
     * @param string $key
     * @return T
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
     * @param T $value
     */
    public function offsetSet($offset, $value) : void
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
    public function offsetExists($offset) : bool
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
    public function offsetUnset($offset): void
    {
        unset($this->data[$offset]);
    }

    /**
     * Array offset get
     *
     * For implements \ArrayAccess.
     *
     * @param T $offset
     * @return T
     */
    public function offsetGet($offset) :mixed
    {
        return $this->data[$offset] ?? null;
    }

    /**
     * Iterator rewind
     *
     * For implements \Iterator (foreach)
     *
     * @return T
     */
    public function rewind() : void
    {
        if (!is_array($this->data))
        {
            $this->data = array();
            return;
        }

        reset($this->data);
    }

    /**
     * Iterator current
     *
     * For implements \Iterator (foreach)
     *
     * @return T
     */
    function current() : mixed
    {
        return current($this->data);
    }

    /**
     * Iterator key
     *
     * For implements \Iterator  (foreach)
     *
     * @return int|null|string
     */
    public function key() :mixed
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
     * @return void
     */
    public function next() : void
    {
        next($this->data);
    }

    /**
     * Iterator valid
     *
     * For implements \Iterator (foreach)
     *
     * @return bool
     */
    public function valid() : bool
    {
        if (!is_array($this->data))
        {
            return false;
        }

        return key($this->data) !== null;
    }

    /**
     * Implements object set
     *
     * @param string $name
     * @param T $value
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
     * @return T
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

    public function jsonSerialize():mixed
    {
        return $this->data;
    }

    public function toJson($options = null)
    {
        return json_encode($this, $options);
    }

    public function __debugInfo()
    {
        return $this->data;
    }
}
