<?php

namespace Db;

/**
 * A where condition for a databse query
 */
class Where implements \Db\Filter
{

    /**
     * The filter
     *
     * @var string
     */
    protected $filter;

    /**
     * The param
     *
     * @var string
     */
    protected $param;

    /**
     * The value
     * @var string
     */
    protected $value;

    /**
     * The condition
     *
     * @var string
     */
    protected $condition = 'and';

    public function __construct($filter = NULL, $param = NULL, $value = NULL, $condition = 'and')
    {
        $param = trim($param);
        $haveIs = stripos($param, 'IS') === 0;
        $hasValue = $value || trim($value) === '0' || trim($value) === 0;

        //support two parameters
        if (!$hasValue && $param && !$haveIs)
        {
            $value = $param;
            $param = is_array($value) ? 'IN' : '=';
        }

        $this->condition = $condition ? $condition : 'and';
        $this->param = $param;
        $this->filter = $filter;
        $this->value = $value;
    }

    public function getCondition()
    {
        return $this->condition;
    }

    public function setCondition($condition)
    {
        $this->condition = $condition ? $condition : 'and';

        return $this;
    }

    public function getParam()
    {
        return $this->param;
    }

    public function setParam($param)
    {
        $this->param = $param;
        return $this;
    }

    public function getFilter()
    {
        return $this->filter;
    }

    public function setFilter($filter)
    {
        $this->filter = $filter;

        return $this;
    }

    public function getValue()
    {
        return $this->getArgs();
    }

    public function getArgs()
    {
        if (is_null($this->value))
        {
            return NULL;
        }

        //always convert to array
        if (!is_array($this->value))
        {
            $this->value = [$this->value];
        }

        //parse for types
        foreach ($this->value as $idx => $value)
        {
            if ($value instanceof \Type\Generic)
            {
                $this->value[$idx] = $value->toDb();
            }
        }

        return $this->value;
    }

    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    public function __toString()
    {
        return $this->getString(false);
    }

    /**
     * @deprecated since version 15/05/2019
     * @param boolean $first if is first where
     * @return string the resultant where
     */
    public function getWhere($first = true)
    {
        return $this->getString($first);
    }

    public function getString($first = false)
    {
        //when it has ? keep as it is
        if (stripos($this->param, '?') !== false)
        {
            $param = $this->param;
        }
        //specifs for IN parameter
        else if ($this->param == 'IN')
        {
            $this->param = $this->param . ' ' . self::parseValuesPdo($this->getValue());
            $param = $this->param;
            $this->value = null;
        }
        else if (stripos($this->param, 'IS') === 0)
        {
            $param = $this->param;
        }
        else
        {
            $param = $this->param ? $this->param . ' ?' : '';
        }

        $where = $this->filter . ' ' . $param . ' ';

        if ($first)
        {
            return $where;
        }
        else
        {
            return strtoupper($this->condition) . ' ' . $where;
        }
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

        return '\'' . $value . '\'';
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

    /**
     * @deprecated since version 15/05/2019
     * @param type $first
     * @return type
     */
    public function getWhereSql($first = true)
    {
        return $this->getStringPdo($first);
    }

    /**
     * Simulate a string like if PDO was mounting it.
     * Or, replace the ? with the values
     *
     * @param boolean $first
     * @return string
     */
    public function getStringPdo($first = false)
    {
        //when it has ? keep as it is
        if (stripos($this->param, '?') !== false)
        {
            $param = str_replace('?', implode(',', $this->getValue()), $this->param);
        }
        //specifs for IN parameter
        else if ($this->param == 'IN')
        {
            $param = $this->param . ' ' . self::parseValuesPdo($this->getValue());
        }
        else if (stripos($this->param, 'IS') === 0)
        {
            $param = $this->param;
        }
        else
        {
            $param = $this->param ? $this->param . ' ' . self::parseValuesPdo($this->getValue()) : '';
        }

        $where = $this->filter . ' ' . $param . ' ';

        if ($first)
        {
            return ' ' . $where;
        }
        else
        {
            return ' ' . strtoupper($this->condition) . ' ' . $where;
        }
    }

    /**
     * Convert a word or prhase to a contains string
     *
     * @param string $word
     * @param string $start
     * @param string $end
     * @return
     */
    public static function contains($word)
    {
        return '%' . str_replace(' ', '%', $word) . '%';
    }

}
