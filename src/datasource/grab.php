<?php

namespace DataSource;

/**
 * A generic porpouse get the get the values of the objects or arrays.
 *
 * It reconize the default Blend objects, like models, contant values and view.
 *
 * And support default PHP stdClass and array
 */
class Grab
{

    /**
     * Return the column name that represents the property in object
     *
     * @param \Component\Grid\Column $column
     * @return string
     */
    public static function getColumnName($column)
    {
        if (is_string($column))
        {
            $columnName = $column;
        }
        elseif ($column instanceof \Component\Grid\Column)
        {
            $columnName = $column->getName();
        }
        elseif ($column instanceof \Db\Column\Column)
        {
            $columnName = $column->getName();
        }

        return \Db\Column\Column::getRealColumnName($columnName);
    }

    /**
     * Return the grid of current dom
     *
     * @return \Component\Grid\Grid
     */
    public static function getGridFromCurrentDom()
    {
        $grid = null;
        $dom = \View\View::getDom();

        if (method_exists($dom, 'getGrid'))
        {
            $grid = $dom->getGrid();
        }

        return $grid;
    }

    /**
     * If is array, convert it to object, to standarlize element
     * and locate things
     *
     * @return object
     *
     */
    public static function standarlizeItem($item)
    {
        if (is_array($item))
        {
            $item = (object) $item;
        }

        return $item;
    }

    /**
     * Get the value from object,
     * Consider get method if it exists
     *
     * @param string $columnName
     * @param object $item
     * @return string
     */
    public static function getDbValueFromObject($columnName, $item)
    {
        if (!$columnName)
        {
            return null;
        }

        $value = null;

        $methodName = 'get' . $columnName;

        if (method_exists($item, $methodName))
        {
            $value = $item->$methodName();
        }
        else if (isset($item->{$columnName}))
        {
            $value = $item->{$columnName};
        }

        return $value;
    }

    /**
     * Find the user value in model
     *
     * @param mixed $column @param mixed $column can be a \Component\Grid\Column or string
     * @param \Db\Model $item
     * @return string
     */
    public static function findInModelUser($column, \Db\Model $item)
    {
        $columnName = self::getColumnName($column);
        $value = $item->getValue($columnName);
        $dbColumn = $item->getColumn($columnName);

        if ($dbColumn)
        {
            $constantValues = $dbColumn->getConstantValues();

            if ($constantValues instanceof \Db\ConstantValues)
            {
                $constantValues = $constantValues->getArray();
            }

            if (isIterable($constantValues))
            {
                if (is_object($value))
                {
                    //if is a generic, get the db value
                    if ($value instanceof \Type\Generic)
                    {
                        $value = $value->toDb();
                    }
                    //if is a default object, convert to string
                    else
                    {
                        $value = $value . '';
                    }
                }

                if (($value || $value == '0') && $constantValues && isset($constantValues[$value]))
                {
                    $value = $constantValues[$value];
                }

                //TODO is this needed yet?
                //add supports for simple object inside collection
                if (is_object($value))
                {
                    //if is a simple object, it presumes second property
                    //is the description, and firs is id
                    $array = array_values((array) $value);

                    if (isset($array[1]))
                    {
                        $value = $array[0] . '-' . $array[1];
                    }
                }
            }
            else if ($dbColumn->getReferenceDescription())
            {
                $columnDescriptionName = $columnName . 'Description';
                $value = $item->getValue($columnDescriptionName);
            }
        }

        return $value;
    }

    /**
     * Find user value in object
     * @param mixed $column can be a \Component\Grid\Column or string
     * @param object $item object or array
     * @return string
     */
    public static function findInObjectUser($column, $item)
    {
        $columnName = self::getColumnName($column);
        $grid = self::getGridFromCurrentDom();
        $ds = $grid ? $grid->getDataSource() : null;
        $value = self::getDbValueFromObject($columnName, $item);

        //add support for description column, when is not a model
        $columnDescriptionName = $columnName . 'Description';

        if (isset($item->$columnDescriptionName) && $item->$columnDescriptionName)
        {
            $value = $item->$columnDescriptionName;
        }

        if ($ds instanceof \DataSource\Model)
        {
            $model = $ds->getModel();
            $dbColumn = $model->getColumn($columnName);

            if ($dbColumn instanceof \Db\Column\Column)
            {
                $constantValues = $dbColumn->getConstantValues();

                if ($constantValues && isset($constantValues [$value]))
                {
                    $value = $constantValues [$value];
                }
            }
        }

        return $value;
    }

    /**
     * Find db value in object
     *
     * @param mixed $column can be a \Component\Grid\Column or string
     * @param object $item object
     * @return string
     */
    public static function findInObjectDb($column, $item)
    {
        $columnName = self::getColumnName($column);
        $grid = self::getGridFromCurrentDom();
        $ds = $grid ? $grid->getDataSource() : null;

        if ($ds instanceof \DataSource\ModelGroup)
        {
            $columns = $ds->getOriginalColumns();

            if (isset($columns[$columnName]))
            {
                $dbColumn = $columns[$columnName];
                $columnName = $dbColumn->getProperty() ? $dbColumn->getProperty() : $columnName;
            }
        }

        $value = self::getDbValueFromObject($columnName, $item);

        return $value;
    }

    /**
     * Return the value of the object (or array) for the columns,
     * uses some magic to get user value
     *
     * @param mixed $column can be a \Component\Grid\Column or string
     * @param mixed $item object or array
     * @return string
     */
    public static function getUserValue($column, $item)
    {
        if (!$column)
        {
            return NULL;
        }

        $item = self::standarlizeItem($item);
        $value = NULL;

        if ($item instanceof \Db\Model)
        {
            $value = self::findInModelUser($column, $item);
        }
        else if (is_object($item))
        {
            $value = self::findInObjectUser($column, $item);
        }

        if ($value instanceof \Type\Generic)
        {
            $value = $value->toHuman();
        }

        //add support for column formatter
        $formatter = $column->getFormatter();

        if ($formatter)
        {
            $formatter->setValue($value);
            $value = $formatter->__toString();
        }

        return $value;
    }

    /**
     * Get database value in some object
     * @param mixed $column can be a \Component\Grid\Column or string
     * @param object $item
     * @return string
     */
    public static function getDbValue($column, $item)
    {
        if (!$column)
        {
            return NULL;
        }

        $value = null;
        $item = self::standarlizeItem($item);
        $columnName = self::getColumnName($column);

        if ($item instanceof \Db\Model)
        {
            $value = $item->getValue($columnName);
        }
        else if (is_object($item))
        {
            $value = self::findInObjectDb($column, $item);
        }

        //add suppor for file, need better automated method for do this
        if ($value instanceof \Disk\File)
        {
            $value = $value->getUrl();
        }

        return $value;
    }

}
