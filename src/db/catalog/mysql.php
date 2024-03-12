<?php

namespace Db\Catalog;

/**
 * Funções especificas para lidar com o catálogo/esquema do mysql
 */
class Mysql implements \Db\Catalog\Base
{

    /**
     * True for database
     */
    const DB_TRUE = '1';

    /**
     * False for datbse
     */
    const DB_FALSE = '0';

    public static function listColums($table, $makeCache = TRUE)
    {
        //no table name, no query
        if (!$table)
        {
            return false;
        }

        //fazer o cache pode ser um processo demorado
        set_time_limit(0);
        //FIXME só funciona para base padrão
        $schema = \Db\Conn::getConnInfo()->getName();
        $cacheKey = $table . '.columns.cache';

        if($makeCache)
        {
            if (\Cache\Cache::exists($cacheKey))
            {
                return \Cache\Cache::get($cacheKey);
            }
        }

        $sql = "
SELECT
t.TABLE_NAME AS tableName,
t.COLUMN_NAME AS name,
t.COLUMN_DEFAULT AS defaultValue,
t.IS_NULLABLE = 'YES' AS nullable,
COALESCE(t.DATA_TYPE, t.NUMERIC_PRECISION) AS type,
t.CHARACTER_MAXIMUM_LENGTH AS size,
t.COLUMN_KEY = 'PRI' AS isPrimaryKey,
t.EXTRA AS extra,
t.COLUMN_COMMENT AS label,
k.REFERENCED_TABLE_NAME AS referenceTable,
k.REFERENCED_COLUMN_NAME AS referenceField,
k.CONSTRAINT_NAME as referenceName
FROM INFORMATION_SCHEMA.COLUMNS t
LEFT JOIN INFORMATION_SCHEMA.KEY_COLUMN_USAGE k
ON t.COLUMN_NAME = k.COLUMN_NAME
AND t.TABLE_SCHEMA = k.CONSTRAINT_SCHEMA
AND t.TABLE_NAME = k.TABLE_NAME
WHERE t.table_name = ?
AND t.table_schema = ?
ORDER BY t.ORDINAL_POSITION;";

        $colums = \Db\Conn::getInstance()->query($sql, array($table, $schema), '\Db\Column\Column');

        if (count($colums) == 0)
        {
            throw new \Exception('Impossível encontrar colunas para a tabela ' . $table);
        }
        else
        {
            //indexa as colunas por nome
            foreach ($colums as $column)
            {
                if (strtolower($column->getType()) == 'int' || strtolower($column->getType()) == 'mediumint')
                {
                    $column->setType(\Db\Column\Column::TYPE_INTEGER);
                }
                else if (strtolower($column->getType()) == 'float')
                {
                    $column->setType(\Db\Column\Column::TYPE_DECIMAL);
                }

                $columns[$column->getName()] = $column;
            }
        }

        if($makeCache && $columns)
        {
            return \Cache\Cache::set($cacheKey, $columns);
        }

        return new \Db\Column\Collection($columns);
    }

    public static function listTables()
    {
        $dbName = \Db\Conn::getConnInfo()->getName();

        $sql = "SELECT TABLE_NAME as name,
                       TABLE_COMMENT as label
                  FROM INFORMATION_SCHEMA.TABLES
                 WHERE TABLE_SCHEMA = ?;";

        return \Db\Conn::getInstance()->query($sql, array($dbName));
    }

    public static function tableExists($table, $makeCache = TRUE)
    {
        if (!$table)
        {
            return null;
        }

        $dbName = \Db\Conn::getConnInfo()->getName();
        $cacheKey = $table . '.table.cache';

        if ($makeCache)
        {
            if (\Cache\Cache::exists($cacheKey))
            {
                return \Cache\Cache::get($cacheKey);
            }
        }

        $sql = "SELECT TABLE_NAME as name,
                       TABLE_COMMENT as label
                  FROM INFORMATION_SCHEMA.TABLES
                 WHERE TABLE_NAME = ?
                 AND TABLE_SCHEMA = ?;
                 ;";

        $tableData = \Db\Conn::getInstance()->query($sql, array($table, $dbName));

        if (isset($tableData[0]))
        {
            if ($makeCache)
            {
                \Cache\Cache::set($cacheKey, $tableData[0]);
            }

            return $tableData[0];
        }

        return null;
    }

    public static function listTableIndex($table = NULL, $indexName = NULL)
    {
        if ($indexName)
        {
            if ($table)
            {
                $result = \Db\Conn::getInstance()->query('SHOW INDEX FROM ' . $table . " WHERE key_name = '{$indexName}'");
            }
            else
            {
                $sql = "
SELECT table_name AS `Table`,
0 as 'Non_unique',
index_name AS `Key_name`,
SEQ_IN_INDEX as 'Seq_in_index',
column_name as 'Column_name',
COLLATION as 'Collation',
CARDINALITY as 'Cardinality',
NULL as 'Sub_part',
NULL as 'Packed',
NULLABLE as 'Null',
INDEX_TYPE as 'Index_type',
COMMENT as 'Comment',
INDEX_COMMENT as 'Index_comment'
FROM information_schema.statistics
WHERE index_name = '{$indexName}'";

                $result = \Db\Conn::getInstance()->query($sql);
            }
        }
        else
        {
            $result = \Db\Conn::getInstance()->query("SHOW INDEX FROM  {$table}");
        }

        return $result;
    }

    public static function mountSelect($tables, $columns, $where = NULL, $limit = NULL, $offset = NULL, $groupBy = NULL, $having = NULL, $orderBy = NULL, $orderWay = NULL, $format = FALSE)
    {
        $lineEnding = $format ? "\r\n" : ' ';
        $sql = 'SELECT' . $lineEnding . $columns;
        $sql .= $tables ? $lineEnding . 'FROM ' . $tables : '';
        $sql .= strlen(trim($where)) > 0 ? $lineEnding . 'WHERE ' . $where : '';
        $sql .= strlen(trim($groupBy)) > 0 ? $lineEnding . 'GROUP BY ' . $groupBy : '';
        $sql .= strlen(trim($having)) > 0 ? $lineEnding . 'HAVING ' . $having : '';
        $sql .= strlen(trim($orderBy)) > 0 ? $lineEnding . 'ORDER BY ' . $orderBy : '';
        $sql .= strlen(trim($orderWay)) > 0 ? ' ' . $orderWay : '';
        $sql .= strlen(trim($limit)) > 0 ? $lineEnding . 'LIMIT ' . $limit : '';

        //avoid negative offset error
        $offset = ( is_numeric(trim($offset)) && trim($offset) < 0) ? 0 : trim($offset);

        $sql .= ( strlen(trim($limit)) > 0 && strlen($offset) > 0 ) ? ' OFFSET ' . $offset : '';

        return $sql;
    }

    public static function mountInsert($tables, $columns, $values, $pk = NULL)
    {
        //pk is not used in this case
        //TODO se why has an pk in insert
        $pk = null;
        return "INSERT INTO $tables ( $columns ) VALUES ( $values ) ";
    }

    public static function mountUpdate($tables, $columns, $where)
    {
        return "UPDATE $tables SET $columns WHERE $where ;";
    }

    public static function mountDelete($tables, $where)
    {
        $where = $where ? 'WHERE ' . $where : '';
        return "DELETE FROM $tables $where;";
    }

    public static function parseColumnNameForQuery($columnName)
    {
        return " `$columnName` = :$columnName";
    }

    public static function parseTableNameForQuery($table)
    {
        //add support for a list of tables
        if (is_array($table))
        {
            foreach ($table as $index => $value)
            {
                $table[$index] = self::parseTableNameForQuery($value);
            }

            return $table;
        }

        //is numeric or function, or has left join
        if (is_numeric($table) ||
                stripos($table, '(') > 0 ||
                stripos($table, '(') === 0 ||
                stripos($table, ' ON ') > 0 ||
                stripos($table, ' ASC') > 0 ||
                stripos($table, ' DESC') > 0
        )
        {
            return trim($table);
        }

        //add support for 'table.field'
        $explode = explode('.', $table);
        $result = null;

        foreach ($explode as $table)
        {
            $table = trim($table);
            $result[] = strlen($table) > 0 ? '`' . $table . '`' : '';
        }

        return implode('.', $result);
    }

    public static function implodeColumnNames($columnNames)
    {
        if (is_array($columnNames))
        {
            foreach ($columnNames as $idx => $columnName)
            {
                //subselect
                $columnName = $columnName . '';
                $hasSelect = stripos($columnName, 'SELECT');
                $hasParentesis = stripos($columnName, '(');
                $hasEscape = stripos($columnName, '`');
                $hasSpace = stripos($columnName, ' ');

                if ($hasSelect || $hasParentesis || $hasEscape || $hasSpace)
                {
                    $columnName = $columnName;
                }
                else
                {
                    $explode = explode(' AS ', $columnName);

                    //as
                    if (count($explode) > 1)
                    {
                        $columnName = '`' . trim($explode[0]) . '` as `' . trim($explode[1]) . '`';
                    }
                    //default simple column
                    else
                    {
                        $columnName = '`' . $columnName . '`';
                    }
                }

                $columnNames[$idx] = $columnName;
            }
        }

        $columns = implode(',', $columnNames);

        return $columns;
    }

    public static function mountCreateTable($name, $comment, $columns, $params)
    {
        $paramStr = '';
        $pksStr = '';
        $columnsStr = '';
        $fksStr = '';

        if (is_array($params))
        {
            foreach ($params as $key => $param)
            {
                $paramStr .= strtoupper($key) . "='" . $param . "'\n";
            }
        }

        $pks = null;
        $fks = null;

        foreach ($columns as $column)
        {
            //avoid searchColumn
            if ($column instanceof \Db\Column\Search)
            {
                continue;
            }

            if ($column->isPrimaryKey())
            {
                $pks[] = '`' . $column->getName() . '`';
            }

            if ($column->getReferenceName())
            {
                $referenceModel = $column->getReferenceTable();
                $referenceTable = $referenceModel::getTableName();
                $fks[] = self::createFk($column->getReferenceName(), $column->getName(), $referenceTable, $column->getReferenceField());
            }

            $columnsStr .= '`' . $column->getName() . '` ' . self::createSqlColumn($column);
            $columnsStr .= ",\n";
        }

        if (is_array($pks))
        {
            $str = implode(',', $pks);
            $pksStr = "PRIMARY KEY ({$str})";
        }
        else
        {
            $columnsStr = rtrim(trim($columnsStr), ',');
            $columnsStr .= "\n";
        }

        if (is_array($fks))
        {
            $fksStr = implode(',', $fks);

            //add the comma
            if ($pksStr)
            {
                $fksStr = ',' . $fksStr;
            }
        }

        $sql = "
CREATE TABLE `{$name}` (
$columnsStr $pksStr
$fksStr
)
COMMENT='$comment'
$paramStr";

        return $sql;
    }

    private static function createSqlColumn(\Db\Column\Column $column)
    {
        if ($column->getSize())
        {
            $type = $column->getType() . '(' . $column->getSize() . ') ';
        }
        else
        {
            $type = $column->getType() . ' ';
        }

        $nullable = $column->isNullable() ? 'NULL ' : 'NOT NULL ';
        $default = $column->getDefaultValue() ? 'DEFAULT \'' . $column->getDefaultValue() . '\' ' : '';

        //special case of current timestamp, can have other cases, need to verify
        if (stripos($column->getDefaultValue(), 'CURRENT_TIMESTAMP') === 0)
        {
            $default = 'DEFAULT ' . $column->getDefaultValue() . ' ';
        }

        $autoIncremento = $column->getExtra() == \Db\Column\Column::EXTRA_AUTO_INCREMENT ? 'AUTO_INCREMENT ' : '';
        $comment = $column->getLabel() ? "COMMENT '" . $column->getLabel() . "'" : '';

        $sql = trim($type . $nullable . $default . $autoIncremento . $comment);

        return $sql;
    }

    public static function mountCreateColumn($tableName, $column, $operation = 'ADD')
    {
        $operation = strtoupper($operation);

        if ($operation != 'ADD')
        {
            $operation = 'CHANGE';
        }

        $tableNameParsed = self::parseTableNameForQuery($tableName);
        $columnNameParsed = self::parseTableNameForQuery($column->getName()) . ' ';

        if ($operation != 'ADD')
        {
            $columnNameParsed .= ' ' . $columnNameParsed;
        }

        $sql = 'ALTER TABLE ' . $tableNameParsed . ' ' . $operation . ' COLUMN ';
        $sql .= $columnNameParsed . self::createSqlColumn($column);

        return $sql;
    }

    protected static function createFk($constraintName, $fields, $referenceTable, $referenceFields)
    {
        $sql = "CONSTRAINT `$constraintName` FOREIGN KEY (`$fields`) REFERENCES `$referenceTable` (`$referenceFields`) ON UPDATE CASCADE";

        return $sql;
    }

    public static function mountCreateFk($tableName, $constraintName, $fields, $referenceTable, $referenceFields)
    {
        $sql = "ALTER TABLE `$tableName` ADD " . self::createFk($constraintName, $fields, $referenceTable, $referenceFields);

        return $sql;
    }

}
