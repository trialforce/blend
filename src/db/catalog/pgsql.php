<?php

namespace Db\Catalog;

/**
 * PGSql Catalog
 */
class PgSql implements \Db\Catalog\Base
{

    /**
     * True for database
     */
    const DB_TRUE = 't';

    /**
     * False for database
     */
    const DB_FALSE = 'f';

    public static function listColums($table, $makeCache = TRUE)
    {
        $cache = null;

        if ($makeCache)
        {
            $cache = new \Db\Cache($table . '.columns.cache');
        }

        if (isset($cache) && is_array($cache->getContent()))
        {
            return $cache->getContent();
        }

        $sql = "
	SELECT
	column_name AS name,
	column_default AS default,
	(is_nullable = 'YES') AS nullable,
	udt_name AS type,
	COALESCE(character_maximum_length,numeric_precision) AS size,
	COALESCE((SELECT indisprimary = TRUE as pk
     FROM pg_index c
LEFT JOIN pg_class t
       ON c.indrelid  = t.oid
LEFT JOIN pg_attribute a
       ON a.attrelid = t.oid
      AND a.attnum = ANY(indkey)
    WHERE t.relname = table_name
      AND a.attname = column_name
      AND indisprimary = TRUE ) , FALSE) AS \"isPrimaryKey\",
	( SELECT column_default like 'nextval%') AS extra,
	( SELECT pg_catalog.col_description(oid,cols.ordinal_position::int)
		FROM pg_catalog.pg_class c
	   WHERE c.relname=cols.table_name) AS label,
	( SELECT ccu.table_name
     FROM information_schema.table_constraints tc
LEFT JOIN information_schema.key_column_usage kcu
       ON tc.constraint_catalog = kcu.constraint_catalog
      AND tc.constraint_schema = kcu.constraint_schema
      AND tc.constraint_name = kcu.constraint_name
LEFT JOIN information_schema.referential_constraints rc
       ON tc.constraint_catalog = rc.constraint_catalog
      AND tc.constraint_schema = rc.constraint_schema
      AND tc.constraint_name = rc.constraint_name
LEFT JOIN information_schema.constraint_column_usage ccu
       ON rc.unique_constraint_catalog = ccu.constraint_catalog
      AND rc.unique_constraint_schema = ccu.constraint_schema
      AND rc.unique_constraint_name = ccu.constraint_name
    WHERE tc.table_name = cols.table_name
      AND tc.constraint_type = 'FOREIGN KEY'
      AND kcu.column_name = cols.column_name )
 AS \"referenceTable\",

( SELECT ccu.column_name
     FROM information_schema.table_constraints tc
LEFT JOIN information_schema.key_column_usage kcu
       ON tc.constraint_catalog = kcu.constraint_catalog
      AND tc.constraint_schema = kcu.constraint_schema
      AND tc.constraint_name = kcu.constraint_name
LEFT JOIN information_schema.referential_constraints rc
       ON tc.constraint_catalog = rc.constraint_catalog
      AND tc.constraint_schema = rc.constraint_schema
      AND tc.constraint_name = rc.constraint_name
LEFT JOIN information_schema.constraint_column_usage ccu
       ON rc.unique_constraint_catalog = ccu.constraint_catalog
      AND rc.unique_constraint_schema = ccu.constraint_schema
      AND rc.unique_constraint_name = ccu.constraint_name
    WHERE tc.table_name = cols.table_name
      AND tc.constraint_type = 'FOREIGN KEY'
      AND kcu.column_name = cols.column_name ) AS \"referenceField\"

	 FROM information_schema.columns cols
    WHERE table_schema = 'public'
      AND table_name = ?
 ORDER BY ordinal_position";

        $colums = \Db\Conn::getInstance()->query($sql, array($table), '\Db\Column');

        if (count($colums) == 0)
        {
            throw new \Exception('Impossível encontrar colunas para a tabela ' . $table);
        }

        foreach ($colums as $column)
        {
            //converte tipos postgres para padrão
            if ($column->getType() == 'int4' || $column->getType() == 'integer')
            {
                $column->setType(\Db\Column::TYPE_INT);
            }

            if ($column->getExtra() == TRUE)
            {
                $column->setExtra(\Db\Model::DB_AUTO_INCREMENT);
            }
        }

        //faz o cache caso necessário
        if (isset($cache) && $colums)
        {
            $cache->save($colums);
        }

        return $colums;
    }

    /**
     * Retorna listagem de tabelas
     *
     * @return array
     */
    public static function listTables()
    {
        $sql = "SELECT tablename AS name,
                       pg_catalog.obj_description(pg_catalog.pg_class.oid) AS label
                  FROM pg_catalog.pg_tables
             LEFT JOIN pg_catalog.pg_class
                    ON pg_catalog.pg_tables.tablename = pg_catalog.pg_class.relname
                 WHERE schemaname NOT IN ('pg_catalog', 'information_schema');";

        return \Db\Conn::getInstance()->query($sql);
    }

    /**
     * Caso a tabela exista retorna um objeto com nome e comentário
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

        if (isset($cache) && is_array($cache->getContent()))
        {
            return $cache->getContent();
        }

        $sql = "SELECT tablename AS table,
                       pg_catalog.obj_description(pg_catalog.pg_class.oid) AS label
                  FROM pg_catalog.pg_tables
             LEFT JOIN pg_catalog.pg_class
                    ON pg_catalog.pg_tables.tablename = pg_catalog.pg_class.relname
                 WHERE schemaname NOT IN ('pg_catalog', 'information_schema')
                   AND tablename = ?";

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

    public static function listTableIndex($table, $indexName = NULL)
    {
        //TODO not implemented
        $table = NULL;
        $indexName = NULL;
        throw new Exception('dbpgsqlcatalog::listTableIndex ainda não implementado');
    }

    public static function mountSelect($tables, $columns, $where = NULL, $limit = NULL, $offset = NULL, $groupBy = NULL, $having = NULL, $orderBy = NULL, $orderWay = NULL)
    {
        $sql = 'SELECT ' . $columns;
        $sql .= $tables ? ' FROM ' . $tables : '';
        $sql .= strlen(trim($where)) > 0 ? ' WHERE ' . $where : '';
        $sql .= strlen(trim($groupBy)) > 0 ? ' GROUP BY ' . $groupBy : '';
        $sql .= strlen(trim($having)) > 0 ? ' HAVING ' . $having : '';
        $sql .= strlen(trim($orderBy)) > 0 ? ' ORDER BY ' . $orderBy : '';
        $sql .= strlen(trim($orderWay)) > 0 ? ' ' . $orderWay : '';
        $sql .= strlen(trim($limit)) > 0 ? ' LIMIT ' . $limit : '';
        $sql .= strlen(trim($offset)) > 0 ? ' OFFSET ' . $offset : '';

        return $sql;
    }

    public static function mountInsert($tables, $columns, $values, $pk = NULL)
    {
        $pk = $pk ? "RETURNING $pk" : '';
        return "INSERT INTO $tables ( $columns ) VALUES ( $values ) $pk";
    }

    public static function mountUpdate($tables, $columns, $where)
    {
        return "UPDATE $tables SET $columns WHERE $where ;";
    }

    public static function mountDelete($tables, $where)
    {
        return "DELETE FROM $tables WHERE $where;";
    }

    public static function parseColumnNameForQuery($columnName)
    {
        return " \"$columnName\" = :$columnName";
    }

    public static function parseTableNameForQuery($table)
    {
        if ($table)
        {
            return '"' . $table . '"';
        }
        else
        {
            return $table;
        }
    }

    public static function implodeColumnNames($columnNames)
    {
        return '"' . implode('", "', $columnNames) . '"';
    }

    public static function mountCreateTable($name, $comment, $columns, $params)
    {
        throw new \Exception('Not implement yet');
    }

    public static function mountCreateColumn($tableName, $column, $operation = 'add')
    {
        throw new \Exception('Not implement yet');
    }

    public static function mountCreateFk($tableName, $constraintName, $fields, $referenceTable, $referenceFields)
    {
        throw new \Exception('Not implement yet');
    }

}
