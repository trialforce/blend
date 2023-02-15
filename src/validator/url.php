<?php
namespace Validator;

/**
 * Url validation (website address)
 */
class Url extends \Validator\Validator
{
    public function validate($value = null)
    {
        $error = parent::validate($value);

        if ( mb_strlen($value) > 0 && !$this->validaUrl($value) )
        {
            $error[ ] = 'Url inválida: '.$value."!";
        }

        return $error;
    }

    protected function validaUrl($value)
    {
        return filter_var($value, FILTER_VALIDATE_URL ) && preg_match('/(http:|https:)\/\/(.*)/', $value);
    }


    /**
     * Add a prefix to url
     *
     * @param $url
     * @param $prefix
     * @return mixed|string
     */
    public static function addPrefix($url, $prefix = 'https://')
    {
        if  ( $ret = parse_url($url) ) {

            if ( !isset($ret["scheme"]) )
            {
                $url = $prefix.$url;
            }
        }

        return $url;
    }

    /**
     * Remove prefix
     * @param $url
     * @return string
     */
    public static function removePrefix($url)
    {
        $prefix = [];
        $prefix[] = 'http://';
        $prefix[] = 'https://';
        $prefix[] = 'ftp://';

        return str_replace($prefix, '', rtrim($url,"/'"));
    }
}
