<?php

namespace Db;

class ConstantValues
{

    /**
     * Retorn an array with value -> description
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

}
