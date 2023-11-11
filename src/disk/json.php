<?php

namespace Disk;

/**
 * Json handler, with error control
 */
class Json
{

    /**
     * Encode a array/object to json string
     *
     * @param mixed $value
     * @param int $options
     * @return string
     *
     * @throws \Exception
     */
    public static function encode($value, $options = 0)
    {
        $result = json_encode($value, $options);

        if ($result)
        {
            return $result;
        }

        throw new \Exception(json_last_error_msg());
    }

    /**
     * Stringfy the array/object as a json string
     * Internally call encode
     *
     * @param mixed $value
     * @param int $options
     * @return string
     * @throws \Exception
     */
    public static function stringfy($value, $options = false)
    {
        return self::encode($value, $options);
    }

    /**
     * Decode json string to \stdClass or array
     *
     * @param string $json json string
     * @param bool $assoc associaty array or not
     * @return mixed
     *
     * @throws \Exception
     */
    public static function decode($json, $assoc = false)
    {
        //convert to utf8 if not
        if (!\Type\Text::isUTF8($json))
        {
            $json = utf8_encode($json);
        }

        //avoid error in null strings
        if (!$json)
        {
            return null;
        }

        $result = json_decode($json, $assoc);

        if ($result)
        {
            return $result;
        }

        throw new \Exception(json_last_error_msg());
    }

    /**
     * Decode a JSON string direct to a PHP Class
     *
     * @param string $json  json string
     * @param string $class php class
     * @param $onlyDefined TRUE only properties defined in objet OR FALSE all properties
     * @return object
     * @throws \ReflectionException
     */
    public static function decodeToClass($json, $class, $onlyDefined = false)
    {
        $result = self::decode($json);

        if (!class_exists($class))
        {
            throw new \Exception('JSON DECODE ERROR: Class '.$class. 'does not exits!');
        }

        $reflection = new \ReflectionClass($class);
        $instance = $reflection->newInstanceWithoutConstructor();

        foreach ($result as $property => $value)
        {
            try
            {
                //if property exists, modify to make fillable
                $instanceProperty = $reflection->getProperty($property);

                if ($instanceProperty)
                {
                    $instanceProperty->setAccessible(true);
                    $instanceProperty->setValue($instance, $value);
                }
            }
            catch (\Throwable $exception)
            {
                //only fill properties defined on objet
                if (!$onlyDefined)
                {
                    //using magic methods
                    $instance->$property = $value;
                }
            }
        }

        return $instance;
    }

    /**
     * Parse json string,same name as JS
     * Internally calls decode
     *
     * @param string $json
     * @param bool $assoc
     * @return mixed
     * @throws \Exception
     */
    public static function parse($json, $assoc = false)
    {
        return self::decode($json, $assoc);
    }

    /**
     * Decode json string from file
     *
     * @param string $path
     * @param bool $assoc
     * @return mixed
     *
     * @throws \Exception
     */
    public static function decodeFromFile($path, $assoc = false)
    {
        if (!file_exists($path))
        {
            throw new \Exception('Json file ' . $path . ' not exists');
        }

        $content = file_get_contents($path);

        if (!$content)
        {
            throw new \Exception('Json file ' . $path . ' is empty');
        }

        return Json::decode($content, $assoc);
    }

}
