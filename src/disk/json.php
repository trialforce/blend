<?php

namespace Disk;

/**
 * Json handler, with error control
 */
class Json
{

    /**
     * Encode an array/object to json string
     *
     * @param mixed $value
     * @param int $options
     * @return string
     *
     * @throws \Exception
     */
    public static function encode($value, $options = 0, $depth = 512)
    {
        $result = json_encode($value, $options,$depth);

        if ($result)
        {
            return $result;
        }

        throw new \Exception(json_last_error_msg());
    }

    /**
     * Encode an array/object to a json string (but formatted)
     * @param $value
     * @param $options
     * @param $depth
     * @return string
     * @throws \Exception
     */
    public static function encodeFormatted($value, $options, $depth = 512)
    {
        $options |= JSON_PRETTY_PRINT;
        return self::encode($value, $options, $depth);
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
            return $result;
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
     * Convert any object of any class to a stdClass using a combination
     * of encode and decode
     *
     * @param mixed $object the passed object
     * @return mixed|null the passed object as stdClass
     * @throws \Exception
     */
    public static function decodeToStdClass($object)
    {
        return \Disk\Json::decode(\Disk\Json::encode($object,null,1024));
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

    /**
     * Forma a json to a beutifull html formated result
     *
     * @param $json
     * @param string $stringColor
     * @param string $numberColor
     * @param string $boolColor
     * @param string $keyColor
     * @return array|string|string[]|null
     * @throws \Exception
     */
    public static function formatToHtml($json, $stringColor = '#080', $numberColor = '#f90', $boolColor = '#b18', $keyColor = '#06d' )
    {
        //optimization
        if (!$json)
        {
            return null;
        }

        try
        {
            $json = \Disk\Json::encode(\Disk\Json::decode($json),JSON_PRETTY_PRINT);
        }
        catch (\Throwable $exception)
        {
            return $json;
        }

        $json = nl2br($json);
        $json = str_replace(" ", '&nbsp;', $json);
        //Strings em verde
        $json = preg_replace('/:(.*?)"([^"]*)"/', ': <span style="color:'.$stringColor.';">"$2"</span>', $json);
        // Números inteiros e de ponto flutuante em laranja
        $json = preg_replace('/:(.*?)([0-9]+\.[0-9]+)/', ': <span style="color:'.$numberColor.';">$2</span>', $json);
        // Chaves em azul
        $json = preg_replace('/"(.*?)":/', '<span style="color:'.$keyColor.';">"$1"</span>:', $json);
        // Booleanos e null em rosa
        $json = preg_replace('/\b(true|false|null)\b/', '<span style="color:'.$boolColor.';">$1</span>', $json);

        return $json;
    }

}
