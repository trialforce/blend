<?php

namespace Db;

/**
 * A field compara condition for a databse query
 */
class Compare extends \Db\Where
{

    public function getString($first = false)
    {
        $where = $this->filter . ' ' . $this->param . ' ' . self::parseValuesPdo($this->getValue());

        if ($first)
        {
            return $where;
        }
        else
        {
            return strtoupper($this->condition) . ' ' . $where;
        }
    }

    protected static function parseValuesPdo($values)
    {
        if (is_array($values))
        {
            //array with one position
            if (count($values) == 1)
            {
                return self::parseValuePdo($values[0]);
            }

            $result = '';

            foreach ($values as $value)
            {
                $result .= self::parseValuePdo($value);
            }

            if (stripos($value, '(') !== 0)
            {
                $result = ' (\'' . $result . '\')';
            }

            return $result;
        }

        return self::parseValuePdo($values);
    }

    protected static function parseValuePdo($value)
    {
        if (is_null($value))
        {
            return '';
        }

        if (stripos($value, '(') === 0)
        {
            return $value;
        }

        return $value;
    }

    public function getStringPdo($first = false)
    {
        return $this->getString($first);
    }

}
