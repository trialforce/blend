<?php

namespace DataSource;

use Db\Column\Column;

/**
 * Bridge class that manage a convertion o \Db\Column\Column to \Component\Grid\Column
 * and vice-versa
 */
class ColumnConvert
{

    protected static function safeName($name)
    {
        return \Type\Text::get($name)->toFile() . '';
    }

    /**
     * Get a simple item o a datasource and convert its property to a list of \Component\Grid\Column
     *
     * If it is a \Db\Model its uses the model columns
     * If it is a stdClass or array it passed trough data mounting \Component\Grid\Column
     *
     * @param mixed $item \Db\Model, stdClass or array
     * @return array
     */
    public static function toGridItemAll($item)
    {
        if ($item instanceof \Db\Model)
        {
            return \DataSource\ColumnConvert::dbToGridAll($item);
        }
        else if (is_object($item))
        {
            //convert to array to use above
            $item = (array) $item;
        }

        if (is_array($item))
        {
            $columns = [];

            foreach ($item as $name => $value)
            {
                $columns[$name] = \DataSource\ColumnConvert::nameToGrid($name, $value);
            }

            return $columns;
        }

        return [];
    }

    /**
     * Convert all \Db\Column\Column to \Component\Grid\Column
     *
     * @param \Db\Model $model array of collumns
     * @return array of \Component\Grid\Column
     */
    public static function dbToGridAll(\Db\Model $model)
    {
        $gridColumns = array();
        $columns = $model->getColumns();
        $label = $model->getLabel();

        if (isIterable($columns))
        {
            foreach ($columns as $column)
            {
                $gridColumn = \DataSource\ColumnConvert::dbToGrid($column);
                $gridColumn->setModelName(get_class($model));
                $gridColumn->setGroupName($label);
                $gridColumns[$column->getName()] = $gridColumn;
            }
        }

        return $gridColumns;
    }

    public static function dbToGridAllGrouped(\Db\Model $model)
    {
        $modelColumns = \DataSource\ColumnConvert::dbToGridAll($model);
        $columnGroup[self::safeName($model->getLabel())] = $modelColumns;
        $relations = $model::getRelations();

        if (isCountable($relations))
        {
            foreach ($relations as $relation)
            {
                $relationColumns = \DataSource\ColumnConvert::dbToGridAll($relation->getModel());

                foreach ($relationColumns as $column)
                {
                    $column->setGroupName($relation->getLabel());
                }

                $columnGroup[self::safeName($relation->getLabel())] = $relationColumns;
            }
        }

        return $columnGroup;
    }

    /**
     * Convert a \Db\Column\Column to \Component\Grid\Column
     *
     * @param Column|null $column db column
     * @param string|null $modelClassName
     * @return \Component\Grid\Column grid column
     */
    public static function dbToGrid(\Db\Column\Column $column = null, $modelClassName = null)
    {
        if (!$column)
        {
            return null;
        }

        $columnLabel = $column->getLabel() ?: $column->getName();

        if ($column->getType() == \Db\Column\Column::TYPE_TIMESTAMP || $column->getType() == \Db\Column\Column::TYPE_DATETIME || $column->getType() == \Db\Column\Column::TYPE_DATE)
        {
            $gridColumn = new \Component\Grid\Column($column->getName(), $columnLabel, \Component\Grid\Column::ALIGN_RIGHT, $column->getType());
        }
        else if ($column->getType() == \Db\Column\Column::TYPE_BOOL || $column->getType() == \Db\Column\Column::TYPE_TINYINT)
        {
            if ($column->getConstantValues())
            {
                $gridColumn = new \Component\Grid\Column($column->getName(), $columnLabel, \Component\Grid\Column::ALIGN_RIGHT, $column->getType());
            }
            else
            {
                $gridColumn = new \Component\Grid\BoolColumn($column->getName(), $columnLabel, \Component\Grid\Column::ALIGN_RIGHT, $column->getType());
            }
        }
        else if ($column->isPrimaryKey())
        {
            $gridColumn = new \Component\Grid\PkColumnEdit($column->getName(), $columnLabel, \Component\Grid\Column::ALIGN_COLAPSE, $column->getType());
        }
        else
        {
            $gridColumn = new \Component\Grid\Column($column->getName(), $columnLabel, \Component\Grid\Column::ALIGN_LEFT, $column->getType());

            if (($column->getType() == \Db\Column\Column::TYPE_INTEGER || $column->getType() == \Db\Column\Column::TYPE_DECIMAL || $column->getType() == \Db\Column\Column::TYPE_TIME) && !$column->getReferenceDescription())
            {
                $gridColumn->setAlign(\Component\Grid\Column::ALIGN_RIGHT);
            }
        }

        //correct the align of integer columns with constant values
        //by default constant values are string, to by default has bo be align to left
        if ($column->getConstantValues())
        {
            $gridColumn->setAlign(\Component\Grid\Column::ALIGN_LEFT);
        }

        $gridColumn->setIdentificator($column->isPrimaryKey());

        //search column has no filter as default
        if ($column instanceof \Db\Column\Search)
        {
            $sqls = $column->getSql(FALSE);

            if (isset($sqls[0]))
            {
                $gridColumn->setSql($sqls[0]);
            }
        }

        //hide text columns by default
        if ($column->getType() === \Db\Column\Column::TYPE_TEXT)
        {
            $gridColumn->setRender(FALSE)->setRenderInDetail(FALSE);
        }

        if ($modelClassName)
        {
            $gridColumn->setModelName($modelClassName);
        }

        $gridColumn->setDbColumn($column);

        return $gridColumn;
    }

    /**
     * Convert a \Component\Grid\Column to \Db\Column\Column
     *
     * @param \Component\Grid\Column|null $column
     * @return \Db\Column\Search $column db column
     */
    public static function gridToDb(\Component\Grid\Column $column = null)
    {
        if (!$column)
        {
            return null;
        }

        $dbColumn = new \Db\Column\Search($column->getLabel(), $column->getName(), $column->getType(), $column->getSql());
        $dbColumn->setValidators(null);

        /*if ($column instanceof \Component\Grid\CustomColumn)
        {
            $outterDbColumn = $column->getDbColumn();
            $dbColumn->setReferenceTable($outterDbColumn->getReferenceTable(), $outterDbColumn->getReferenceField(), $outterDbColumn->getReferenceDescription());
            $dbColumn->setConstantValues($outterDbColumn->getConstantValues());
        }*/

        return $dbColumn;
    }

    public static function gridToDbAll(array $gridColumns)
    {
        $result = [];

        foreach ($gridColumns as $gridColumn)
        {
            $column = self::gridToDb($gridColumn);
            $result[$column->getName()] = $column;
        }

        return $result;
    }

    /**
     * Convert a simple property name and its value to a \Component\Grid\Coumn
     *
     * @param string $name column name
     * @param string $value column value, only used to dected if the column is a number (and align it right)
     * @return \Component\Grid\Column
     */
    public static function nameToGrid($name = null, $value = null)
    {
        if (!$name)
        {
            return null;
        }

        //support for private variables
        $name = str_replace(' * ', '', $name);
        $label = $name;

        //if don't has spaces consider as a lowerCameCase pattern
        if (stripos($label ,' ') === false)
        {
            $label = ucfirst(strtolower(implode(' ',preg_split('/(?=[A-Z])/',$label))));
        }

        //if is numeric align to right
        $align = \Type\Integer::isNumeric($value . '') ? \Component\Grid\Column::ALIGN_RIGHT : \Component\Grid\Column::ALIGN_LEFT;

        return new \Component\Grid\Column($name, $label, $align);
    }

    /**
     * Convert a PkColumnEdit to a simple grid column
     *
     * @param \Component\Grid\PkColumnEdit $gridColumn
     * @return \Component\Grid\Column
     */
    public static function gridPkColumnToSimple(\Component\Grid\PkColumnEdit $gridColumn)
    {
        $gridColumnNew = new \Component\Grid\Column;
        $gridColumnNew->setName($gridColumn->getName());
        $gridColumnNew->setLabel($gridColumn->getLabel());
        $gridColumnNew->setModelName($gridColumn->getModelName());
        $gridColumnNew->setGroupName($gridColumn->getGroupName());
        $gridColumnNew->setSql($gridColumn->getSql());
        $gridColumnNew->setType($gridColumn->getType());
        $gridColumnNew->setIdentificator($gridColumn->getIdentificator());
        $gridColumnNew->setRender($gridColumn->getRender());
        $gridColumnNew->setRenderInDetail($gridColumn->getRenderInDetail());
        $gridColumnNew->setExport($gridColumn->getExport());
        $gridColumnNew->setSmartFilter($gridColumn->getSmartFilter());
        $gridColumnNew->setOrder($gridColumn->getOrder());
        $gridColumnNew->setUserAdded($gridColumn->getUserAdded());
        $gridColumnNew->setFormatter($gridColumn->getFormatter());
        $gridColumnNew->setDbColumn($gridColumn->getDbColumn());

        return $gridColumnNew;
    }

}
