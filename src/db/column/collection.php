<?php

namespace Db\Column;

/**
 * Column collection
 */
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

    public function __construct($columns = null)
    {
        $this->setColumns($columns);
    }

    public function jsonSerialize() : mixed
    {
        return $this->columns;
    }

    public function count() : int
    {
        return count($this->columns);
    }

    public function current() :mixed
    {
        return current($this->columns);
    }

    public function key() : mixed
    {
        if (!is_array($this->columns))
        {
            return null;
        }

        return key($this->columns);
    }

    public function next() : void
    {
        next($this->columns);
    }

    public function offsetExists($offset) :bool
    {
        return isset($this->columns[$offset]);
    }

    public function offsetGet($offset):mixed
    {
        return isset($this->columns[$offset]) ? $this->columns[$offset] : null;
    }

    public function offsetSet($offset, $value): void
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

    public function offsetUnset($offset) : void
    {
        unset($this->columns[$offset]);
    }

    public function rewind():void
    {
        if (!is_array($this->columns))
        {
            $this->columns = array();
            return;
        }

        reset($this->columns);
    }

    public function valid() : bool
    {
        if (!is_array($this->columns))
        {
            return false;
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

    public function removeColumn($columnName)
    {
        if (isset($this->columns[$columnName]))
        {
            unset($this->columns[$columnName]);
        }

        return $this;
    }

    public function setColumn(\Db\Column\Column $column, $position = null)
    {
        if (is_int($position))
        {
            $new = [];
            $columnName = $column->getName() . '';
            $new[$columnName] = $column;

            if ($this->columnExist($columnName))
            {
                $this->removeColumn($columnName);
            }

            $columns = $this->getColumns();

            array_splice($columns, $position, 0, $new);

            $this->setColumns($columns);
        }
        else
        {
            $this->columns[$column->getName()] = $column;
        }

        return $this;
    }

    public function addColumns(array $columns)
    {
        foreach ($columns as $column)
        {
            if ($column instanceof \Db\Column\Column)
            {
                $this->setColumn($column);
            }
        }

        return $this;
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

        //find the collumn by property in the end
        foreach ($columns as $column)
        {
            if ($column->getProperty() == $columnName)
            {
                return $column;
            }
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

    public static function getSchemaClassName($modelName)
    {
        $schemaClass = str_replace(array('\Model\\', '\Api\\'), '\Schema\\', $modelName);

        return $schemaClass;
    }

    /**
     * Get/define columns for model
     *
     * 1 - Search cached columns
     * 2 - Search for folder schema
     * 3 - Search for defineColumns in model
     * 4 - Mount based on query in database table
     *
     * @param string $modelName model name
     * @return \Db\Column\Collection
     */
    public static function getForModel($modelName)
    {
        $tableName = $modelName::getTableName();

        //1 - Search cached columns
        if (isset(self::$columnsCache[$modelName]))
        {
            return self::$columnsCache[$modelName];
        }

        $schemaClass = self::getSchemaClassName($modelName);

        //2 - Search for folder schema
        if (class_exists($schemaClass))
        {
            $columns = new $schemaClass();
        }
        //3 - Search for defineColumns in model
        else if (method_exists($modelName, 'defineColumns'))
        {
            $columns = $modelName::defineColumns();

            if (!$columns instanceof \Db\Column\Collection)
            {
                $columns = new \Db\Column\Collection($columns);
            }
        }
        else
        {
            //4 - Mount based on query in database table
            $catalog = $modelName::getCatalogClass();
            $modelName = '\Model\\' . str_replace('\Model\\', '', $tableName);
            $columns = $catalog::listColums($modelName::getTableName());
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
