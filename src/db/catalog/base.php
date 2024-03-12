<?php

namespace Db\Catalog;

/**
 * Base catalog.
 * A \Db\Catalog\Base is a class to make a bridge by OO and databse
 */
interface Base
{

    /**
     * List tables of current databse
     */
    public static function listTables();

    /**
     * Verify if some table exists
     *
     * @param string $table table name
     * @param bool $makeCache use cache
     */
    public static function tableExists($table, $makeCache = TRUE);

    /**
     * List table columns
     * @param string $table table name
     * @param bool $makeCache make cache
     */
    public static function listColums($table, $makeCache = TRUE);

    /**
     *  List table indexes
     * @param string $table table name
     * @param string $indexName index name (pass null to get all indexes of table)
     */
    public static function listTableIndex($table = NULL, $indexName = NULL);

    /**
     * Mount one SQL select command
     *
     * @param string $tables tables (table and left join)
     * @param string $columns columns of the query
     * @param string $where where criteria
     * @param int $limit query limit
     * @param int $offset query offset
     * @param string $groupBy query group by
     * @param string $having having
     * @param string $orderBy order by
     * @param string $orderWay order way (ASC, DESC)
     * @param bool $format format or not the sql output
     */
    public static function mountSelect($tables, $columns, $where = NULL, $limit = NULL, $offset = NULL, $groupBy = NULL, $having = NULL, $orderBy = NULL, $orderWay = NULL, $format = FALSE);

    /**
     * Mount a SQL insert command
     *
     * @param string $tables table name
     * @param string $columns list of columns
     * @param string $values values
     * @param int $pk primary key
     */
    public static function mountInsert($tables, $columns, $values, $pk = NULL);

    /**
     * Mount SQL update command
     *
     * @param string $tables table name
     * @param string $columns columns
     * @param string $where where criteria
     */
    public static function mountUpdate($tables, $columns, $where);

    /**
     * Mount SQL delte command
     *
     * @param string $tables table name
     * @param string $where query criteria
     */
    public static function mountDelete($tables, $where);

    /**
     * Adjust column name name for query on databse
     *
     * @param string $columnName column name
     */
    public static function parseColumnNameForQuery($columnName);

    /**
     * Adjust table name for query on databse
     *
     * @param string $table table name
     */
    public static function parseTableNameForQuery($table);

    /**
     * Implode the column names
     *
     * @param string $columnNames
     */
    public static function implodeColumnNames($columnNames);

    /**
     * Create one table
     *
     * @param string $name table name
     * @param string $comment comnent or table label
     * @param array $columns list of \Db\Column\Column
     * @param array $params extra parans for table creation
     */
    public static function mountCreateTable($name, $comment, $columns, $params);

    /**
     * Create or change one table
     *
     * @param string $tableName table name
     * @param \Db\Column\Column $column column object
     * @param string $operation operation
     */
    public static function mountCreateColumn($tableName, $column, $operation = 'add');

    /**
     * Mount a sql to create a foreign key
     *
     * @param string $tableName
     * @param string $constraintName
     * @param string $fields
     * @param string $referenceTable
     * @param string $referenceFields
     */
    public static function mountCreateFk($tableName, $constraintName, $fields, $referenceTable, $referenceFields);
}
