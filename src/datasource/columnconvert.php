<?php

namespace DataSource;

/**
 * Bridge class that manage a convertion o \Db\Column\Column to \Component\Grid\Column
 * and vice-versa
 */
class ColumnConvert
{

    /**
     * Get a simple item o a datasource and convert its property to a lista of \Component\Grid\Column
     *
     * If it is a \Db\Model its uses the model columns
     * If it is a stdClass or array it passed trough data mounting \Component\Grid\Column
     *
     * @param mixed $item \Db\Model, stdClass or array
     * @return type
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
            foreach ($item as $name => $value)
            {
                $columns[$name] = \DataSource\ColumnConvert::nameToGrid($name, $value);
            }

            return $columns;
        }
    }

    /**
     * Convert all \Db\Column\Column to \Component\Grid\Column
     *
     * @param array $columns array of collumns
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
        $relations = $model::getRelations();
        $columnGroup[self::safeName($model->getLabel())] = $modelColumns;

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

    protected static function safeName($name)
    {
        return \Type\Text::get($name)->toFile() . '';
    }

    /**
     * Convert a \Db\Column\Column to \Component\Grid\Column
     *
     * @param \Db\Column\Column $column db column
     * @return \Component\Grid\Column grid column
     */
    public static function dbToGrid(\Db\Column\Column $column = null, $modelClassName = null)
    {
        if (!$column)
        {
            return null;
        }

        $columnLabel = $column->getLabel() ? $column->getLabel() : $column->getName();

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

            //$gridColumn->setFilter(FALSE);
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
     * @param \Component\Grid\Column grid column
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

        if ($column instanceof \Component\Grid\CustomColumn)
        {
            $outterDbColumn = $column->getDbColumn();
            $dbColumn->setReferenceTable($outterDbColumn->getReferenceTable(), $outterDbColumn->getReferenceField(), $outterDbColumn->getReferenceDescription());
            $dbColumn->setConstantValues($outterDbColumn->getConstantValues());
        }

        return $dbColumn;
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
        $name = str_replace(' * ', '', $name);
        $align = \Component\Grid\Column::ALIGN_LEFT;

        //if is numeric align to right
        if (\Type\Integer::isNumeric($value . ''))
        {
            $align = \Component\Grid\Column::ALIGN_RIGHT;
        }

        return new \Component\Grid\Column($name, $name, $align);
    }

}
