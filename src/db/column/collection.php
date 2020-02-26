<?php

namespace Db\Column;

class Collection implements \ArrayAccess, \Iterator, \Countable, \JsonSerializable
{

    /**
     * Columns for cache, avoid large memory usage
     *
     * @var array
     */
    protected static $columnsCache;

    /**
     * Column list
     * @var array
     */
    protected $columns = array();

    public function __construct($columns)
    {
        $this->setColumns($columns);
    }

    public function jsonSerialize()
    {
        return $this->columns;
    }

    public function count()
    {
        return count($this->columns);
    }

    public function current()
    {
        return current($this->columns);
    }

    public function key()
    {
        if (!is_array($this->columns))
        {
            return null;
        }

        return key($this->columns);
    }

    public function next()
    {
        return next($this->columns);
    }

    public function offsetExists($offset)
    {
        return isset($this->columns[$offset]);
    }

    public function offsetGet($offset)
    {
        return isset($this->columns[$offset]) ? $this->columns[$offset] : null;
    }

    public function offsetSet($offset, $value)
    {
        if (is_null($offset))
        {
            $this->columns[] = $value;
        }
        else
        {
            $this->columns[$offset] = $value;
        }
    }

    public function offsetUnset($offset)
    {
        unset($this->columns[$offset]);
    }

    public function rewind()
    {
        if (!is_array($this->columns))
        {
            $this->columns = array();
            return null;
        }

        return reset($this->columns);
    }

    public function valid()
    {
        if (!is_array($this->columns))
        {
            return null;
        }

        return key($this->columns) !== null;
    }

    public function getColumns()
    {
        return $this->columns;
    }

    public function setColumns($columns)
    {
        $this->columns = $columns;
        return $this;
    }

    public function setColumn(\Db\Column\Column $column)
    {
        $this->columns[$column->getName()] = $column;
    }

    function getColumn($columnName = null)
    {
        if (!$columnName)
        {
            return null;
        }

        //maximize compability
        if ($columnName instanceof \Db\Column\Column)
        {
            return $columnName;
        }

        $columnName = \Db\Column\Column::getRealColumnName($columnName);
        $columns = $this->getColumns();

        if (isset($columns) && isset($columns[$columnName]))
        {
            return $columns[$columnName];
        }

        return null;
    }

    public function getColumnAtPosition($position = 0)
    {
        $columns = array_values($this->columns);

        return isset($columns[$position]) ? $columns[$position] : null;
    }

    public function columnExist($columnName)
    {
        $columns = $this->getColumns();

        foreach ($columns as $col)
        {
            if ($col->getName() == $columnName)
            {
                return TRUE;
            }
        }

        return FALSE;
    }

    public function getPrimaryKeys()
    {
        $pk = array();

        $columns = $this->getColumns();

        foreach ($columns as $column)
        {
            if ($column instanceof \Db\Column\Column && $column->isPrimaryKey())
            {
                $pk[$column->getName()] = $column;
            }
        }

        return $pk;
    }

    /**
     * Return the first primary key, normally id collumn
     *
     * @return \Db\Column\Column
     */
    public function getPrimaryKey()
    {
        $pksV = array_values($this->getPrimaryKeys());

        return isset($pksV[0]) ? $pksV[0] : null;
    }

    public function getSqlNamesForFind()
    {
        $result = array();
        $columns = $this->getColumns();

        foreach ($columns as $column)
        {
            $line = $column->getSql();

            if (is_array($line))
            {
                $result = array_merge($line, $result);
            }
        }

        return $result;
    }

    public static function setForModel($modelName, $columns)
    {
        $columns = $columns instanceof \Db\Column\Collection ? $columns : new \Db\Column\Collection($columns);
        self::$columnsCache[$modelName] = $columns;
    }

    /**
     * Choode columns between passed (if exists) or default model columns
     *
     * @param string $modelName model name
     * @param \Db\Column\Collection $columns array
     * @return \Db\Column\Collection the column
     */
    public static function chooseForModel($modelName, $columns = NULL)
    {
        if (is_array($columns))
        {
            return new \Db\Column\Collection($columns);
        }
        else if ($columns instanceof \Db\Column\Collection)
        {
            return $columns;
        }
        else
        {
            return \Db\Column\Collection::getForModel($modelName);
        }
    }

    /**
     * Get/define columns for model
     *
     * @param string $modelName model name
     * @return \Db\Column\Collection
     */
    public static function getForModel($modelName)
    {
        $tableName = $modelName::getTableName();

        //get information from cache
        if (isset(self::$columnsCache[$modelName]))
        {
            return self::$columnsCache[$modelName];
        }

        //try to locate method in child tables
        if (method_exists($modelName, 'defineColumns'))
        {
            $columns = $modelName::defineColumns();

            if (!$columns instanceof \Db\Column\Collection)
            {
                $columns = new \Db\Column\Collection($columns);
            }
        }
        else
        {
            //or, get from database
            $catalog = $modelName::getCatalogClass();
            $columns = $catalog::listColums($tableName::getTableName());
        }

        //define the tablename of columns in needed
        foreach ($columns as $column)
        {
            $column->setTableName($tableName);
        }

        self::$columnsCache[$modelName] = $columns;

        return $columns;
    }

}
