<?php

namespace Disk;

/**
 * Json handler, with error control
 */
class Json
{

    /**
     * Encode a mixed value to json string
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

        throw new \Exception(self::getLastErrorMsg());
    }

    /**
     * Decode json string
     *
     * @param string $json
     * @param bool $assoc
     * @return mixed
     *
     * @throws \Exception
     */
    public static function decode($json, $assoc = false)
    {
        //convert to ut8 if not
        if (!\Type\Text::isUTF8($json))
        {
            $json = utf8_encode($json);
        }

        $result = json_decode($json, $assoc);

        if ($result)
        {
            return $result;
        }

        throw new \Exception(self::getLastErrorMsg());
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
     * Get last json error
     * add compatibility with old php versions
     *
     * @return string
     */
    public static function getLastErrorMsg()
    {
        if (!function_exists('json_last_error_msg'))
        {
            return json_last_error_msg();
        }
        else
        {
            switch (json_last_error())
            {
                default:
                    return;
                case JSON_ERROR_DEPTH:
                    $error = 'Maximum stack depth exceeded';
                    break;
                case JSON_ERROR_STATE_MISMATCH:
                    $error = 'Underflow or the modes mismatch';
                    break;
                case JSON_ERROR_CTRL_CHAR:
                    $error = 'Unexpected control character found';
                    break;
                case JSON_ERROR_SYNTAX:
                    $error = 'Syntax error, malformed JSON';
                    break;
                case JSON_ERROR_UTF8:
                    $error = 'Malformed UTF-8 characters, possibly incorrectly encoded';
                    break;
            }

            return $error;
        }
    }

}