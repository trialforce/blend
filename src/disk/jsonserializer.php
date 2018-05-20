<?php

namespace Disk;
use Disk\Json;

/**
 * Json serializer
 * Extend this class to make your object serializable trough json
 */
class JsonSerializer
{

    /**
     * Convert an object to a simple array
     *
     * @return array
     */
    public static function getArray($variable, $putClassName = false)
    {
        if (!(is_array($variable) || is_object($variable)))
        {
            return $variable;
        }

        $temp = (array) ($variable);
        $array = array();

        $phpClassName = NULL;

        if (is_object($variable))
        {
            $phpClassName = '\\' . get_class($variable);
        }

        $propertysToAvoid = array();

        if ($variable instanceof \Disk\JsonAvoidPropertySerialize)
        {
            $propertysToAvoid = $variable->listAvoidPropertySerialize();
        }

        foreach ($temp as $key => $value)
        {
            //correct private and protected methods
            $key = preg_match('/^\x00(?:.*?)\x00(.+)/', $key, $matches) ? $matches[1] : $key;

            //avoid propertys to be avoided
            if (in_array($key, $propertysToAvoid))
            {
                continue;
            }

            //parse object
            if (is_object($value))
            {
                $value = self::getArray($value, $putClassName);
            }
            //parse array
            else if (is_array($value))
            {
                $v = null;

                foreach ($value as $mKey => $info)
                {
                    $value[$mKey] = self::getArray($info, $putClassName);
                }
            }

            $array[$key] = $value;
        }

        if ($putClassName && $phpClassName)
        {
            $array = array_merge(array('phpClassName' => $phpClassName), $array);
        }

        return $array;
    }

    /**
     * Create an object from a json array
     *
     * @param array $json
     * @return \Disk\className
     */
    protected static function createFromArray($json)
    {
        //simple data
        if (!is_array($json))
        {
            return $json;
        }

        //add suporte for an array of objects
        if (!isset($json['phpClassName']))
        {
            $result = NULL;

            foreach ($json as $key => $value)
            {
                $result[$key] = self::createFromArray($value);
            }

            return $result;
        }

        $className = $json['phpClassName'];
        $obj = new $className();

        foreach ($json as $key => $value)
        {
            //avoid phpClassName property
            if ($key == 'phpClassName')
            {
                continue;
            }
            else if (is_object($value))
            {
                $value = self::createFromArray($value);
            }
            else if (is_array($value))
            {
                if (isset($value['phpClassName']))
                {
                    $value = self::createFromArray($value);
                }
                else
                {
                    $myArray = NULL;

                    foreach ($value as $mKey => $info)
                    {
                        $myArray[$mKey] = self::createFromArray($info);
                    }

                    $value = $myArray;
                }
            }

            $methodName = 'set' . $key;

            //detect set method
            if (method_exists($obj, $methodName))
            {
                $obj->$methodName($value);
            }
            else
            {
                $obj->$key = $value;
            }
        }

        return $obj;
    }

    /**
     * Encode a variable to json
     *
     * @param mixed $variable
     * @return string
     */
    public static function encode($variable)
    {
        return Json::encode(self::getArray($variable, TRUE), JSON_PRETTY_PRINT);
    }

    /**
     * Create an object from a serialized json
     *
     * @param string $json
     * @return mixed
     */
    public static function decode($json)
    {
        $array = Json::decode($json, true);
        return self::createFromArray($array);
    }

    /**
     * Create a object from a serialized json file
     *
     * @param string $path
     * @return mixed
     */
    public static function decodeFromFile($path)
    {
        $array = Json::decodeFromFile($path, TRUE);
        return self::createFromArray($array);
    }

    /**
     * Encode a variable to a file
     *
     * @param mixed $variable
     * @param string $path
     * @return bool
     */
    public static function encodeToFile($variable, $path)
    {
        $content = Json::encode(self::getArray($variable, TRUE), JSON_PRETTY_PRINT);

        if (!$path)
        {
            throw new \Exception('JsonSerializer: encodeToFile error, path empty!');
        }

        return file_put_contents($path, $content);
    }

}