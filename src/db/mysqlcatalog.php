<?php

namespace Db;

/**
 * Funções especificas para lidar com o catálogo/esquerma do mysql
 */
class MysqlCatalog
{

    /**
     * Verdadeiro para o banco
     */
    const DB_TRUE = '1';

    /**
     * Falso para o banco
     */
    const DB_FALSE = '0';

    /**
     * Lista as colunas de uma tabela
     *
     * @param array $table de \Db\Column
     */
    public static function listColums($table, $makeCache = TRUE)
    {
        //fazer o cache pode ser um processo demorado
        set_time_limit(0);
        //FIXME só funciona para base padrão
        $schema = \Db\Conn::getConnInfo()->getName();
        $cache = null;

        if ($makeCache)
        {
            $cache = new \Db\Cache($table . '.columns.cache');
        }

        if (isset($cache) && is_array($cache->getContent()))
        {
            return $cache->getContent();
        }

        $sql = "SELECT
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
                k.REFERENCED_COLUMN_NAME AS referenceField
                FROM INFORMATION_SCHEMA.COLUMNS t
                    LEFT JOIN INFORMATION_SCHEMA.KEY_COLUMN_USAGE k ON t.COLUMN_NAME = k.COLUMN_NAME
                                                                    AND t.TABLE_SCHEMA = k.CONSTRAINT_SCHEMA
                                                                    AND t.TABLE_NAME = k.TABLE_NAME
                  WHERE t.table_name = ?
                    AND t.table_schema = ?
               ORDER BY t.ORDINAL_POSITION;";

        $colums = \Db\Conn::getInstance()->query($sql, array($table, $schema), '\Db\Column');

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
                    $column->setType(\Db\Column::TYPE_INTEGER);
                }

                $columns[$column->getName()] = $column;
            }
        }

        //faz o cache caso necessário
        if (isset($cache) && $columns)
        {
            $cache->save($columns);
        }

        return $columns;
    }

    /**
     * Retorna listagem de tabelas
     *
     * @return array
     */
    public static function listTables()
    {
        $dbName = \Db\Conn::getConnInfo()->getName();

        $sql = "SELECT TABLE_NAME as name,
                       TABLE_COMMENT as label
                  FROM INFORMATION_SCHEMA.TABLES
                 WHERE TABLE_SCHEMA = ?;";

        return \Db\Conn::getInstance()->query($sql, array($dbName));
    }

    /**
     * If table exists return an stdClass with name and comment
     *
     * @param string $table
     * @return \stdClass
     */
    public static function tableExists($table, $makeCache = TRUE)
    {
        if ($makeCache)
        {
            $cache = new \Db\Cache($table . '.table.cache');
        }

        if (isset($cache) && is_object($cache->getContent()))
        {
            return $cache->getContent();
        }

        $sql = "SELECT TABLE_NAME as name,
                       TABLE_COMMENT as label
                  FROM INFORMATION_SCHEMA.TABLES
                 WHERE TABLE_NAME = ?;";

        $tableData = \Db\Conn::getInstance()->query($sql, array($table));

        if (isset($tableData[0]))
        {
            if (isset($cache))
            {
                $cache->save($tableData[0]);
            }

            return $tableData[0];
        }

        return null;
    }

    /**
     * Aqui o Rene deveria ter adicionado um comentário
     * explicando para que serve essa função.
     *
     * @param string $table
     * @param string $indexName
     * @return type
     */
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

    /**
     * Monta uma string de um select.
     *
     * @param string $columns as colunas
     * @param string $tables as tabelas, ou tabela
     * @param string $where as condições, caso existam
     * @param string $limit o limite caso exista
     * @param string $offset o offset caso exist
     * @param string $groupBy agrupamento
     * @param string $having having
     * @param string $orderBy ordernação, caso exista
     *
     * @return string
     */
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

    /**
     * Monta um sql de insert
     *
     * @param string $columns
     * @param string $tables
     * @param string $values
     * @param string $pk não usado no mysql
     *
     * @return string
     */
    public static function mountInsert($tables, $columns, $values, $pk = NULL)
    {
        //pk is not used in this case
        //TODO se why has an pk in insert
        $pk = null;
        return "INSERT INTO $tables ( $columns ) VALUES ( $values ) ";
    }

    /**
     * Retorna o sql para o update
     *
     * @param string $columns
     * @param string $tables
     * @param string $where
     * @return string
     */
    public static function mountUpdate($tables, $columns, $where)
    {
        return "UPDATE $tables SET $columns WHERE $where ;";
    }

    /**
     * Retorna um sql de remoção
     *
     * @param string $tables
     * @param string $where
     * @return string
     */
    public static function mountDelete($tables, $where)
    {
        return "DELETE FROM $tables WHERE $where;";
    }

    /**
     * Ajusta o campo conforme a necessidade do bando
     *
     * @param string $columnName
     * @return string
     *
     */
    public static function parseColumnNameForQuery($columnName)
    {
        return " `$columnName` = :$columnName";
    }

    /**
     * Adjust the name for the query
     * Support array as parameter
     *
     * @param string or array $table
     * @return string
     */
    public static function parseTableNameForQuery($table)
    {
        if (is_array($table))
        {
            foreach ($table as $index => $value)
            {
                $table[$index] = self::parseTableNameForQuery($value);
            }

            return $table;
        }

        //is numeric or function
        if (is_numeric($table) || stripos($table, '(') > 0 || stripos($table, '(') === 0)
        {
            return trim($table);
        }

        return strlen(trim($table)) > 0 ? '`' . trim($table) . '`' : '';
    }

    /**
     * Junto os campos usando o separador do banco
     *
     * @param string $columnNames
     * @return string
     *
     */
    public static function implodeColumnNames($columnNames)
    {
        if (is_array($columnNames))
        {
            foreach ($columnNames as $idx => $columnName)
            {
                //subselect
                if (stripos($columnName, 'SELECT') || stripos($columnName, '(') || stripos($columnName, ' ' || stripos($columnName, '`')))
                {
                    $columnName = $columnName;
                }
                else
                {
                    $explode = explode('AS', $columnName);

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

        //$columns = '`'.implode('`,`', $columnNames).'`';
        $columns = implode(',', $columnNames);

        return $columns;
    }

}

if (!class_exists('\Db\Catalog'))
{

    class Catalog extends \Db\MysqlCatalog
    {

    }

}