<?php

namespace Db;

/**
 * A where condition for a databse query
 */
class Where implements \Db\Filter
{

    /**
     * Normal where
     */
    const TYPE_NORMAL = '';

    /**
     * Having where, define that the condiction has to be inserted in having
     * part of the query
     */
    const TYPE_HAVING = 'having';

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
    protected $condition;

    /**
     * Condition type
     */
    protected $type;

    public function __construct($filter = NULL, $param = NULL, $value = NULL, $condition = 'and', $type = self::TYPE_NORMAL)
    {
        $this->condition = $condition ? $condition : 'and';
        $this->param = $param;
        $this->filter = $filter;
        $this->value = $value;
        $this->type = $type;
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

    public function getType()
    {
        return $this->type;
    }

    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

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

    public function getWhereSql($first = true)
    {
        return $this->getStringPdo($first);
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

    public function __toString()
    {
        return $this->getWhere(false);
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
