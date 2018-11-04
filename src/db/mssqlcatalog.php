<?php

namespace Db;

/**
 * Funções especificas para lidar com o catálogo/esquerma do mysql
 */
class MssqlCatalog
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
	'$table' as tableName,
    c.name as name,
	'' as defaultValue,
    c.is_nullable as nullable,
    t.Name as type,
    c.max_length as size,
    ISNULL(i.is_primary_key, 0) as isPrimaryKey,
	'' as extra,
    c.name AS label,
    '' AS referenceTable,
    '' AS referenceField
FROM
 sys.columns c
INNER JOIN sys.types t ON c.user_type_id = t.user_type_id
LEFT OUTER JOIN sys.index_columns ic ON ic.object_id = c.object_id AND ic.column_id = c.column_id
LEFT OUTER JOIN sys.indexes i ON ic.object_id = i.object_id AND ic.index_id = i.index_id
WHERE c.object_id = OBJECT_ID('$table')";

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
                $column->setReferenceTable(trim($column->getReferenceTable()));
                $column->setDefaultValue(trim($column->getDefaultValue()));
                $column->setReferenceField(trim($column->getReferenceField()));
                $column->setExtra(trim($column->getExtra()));

                if (strtolower($column->getType()) == 'int' || strtolower($column->getType()) == 'mediumint')
                {
                    $column->setType(\Db\Column::TYPE_INTEGER);
                }

                $type = $column->getType();

                if (strtolower($type) == 'char')
                {
                    $type = \Db\Column::TYPE_VARCHAR;
                }
                else if (strtolower($type) == 'int' || strtolower($type) == 'smallint' || strtolower($type) == 'numeric')
                {
                    $type = \Db\Column::TYPE_INTEGER;
                }
                else if (strtolower($type) == 'float')
                {
                    $type = \Db\Column::TYPE_DECIMAL;
                }
                else if (strtolower($type) == 'bit')
                {
                    $type = \Db\Column::TYPE_INTEGER;
                }
                else if (strtolower($type) == 'image')
                {
                    $type = \Db\Column::TYPE_UNKNOW;
                }

                $column->setType($type);

                $label = str_replace('_', ' ', $column->getLabel());
                $label = str_replace('_', ' ', $label);
                $label = str_replace('_', ' ', $label);
                $label = str_replace('Id', '', $label);
                $label = ucfirst(strtolower($label));
                $column->setLabel(trim($label));

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

        $sql = "SELECT
    t.NAME AS name,
	t.NAME AS label
FROM
    sys.tables t
INNER JOIN
    sys.indexes i ON t.OBJECT_ID = i.object_id
INNER JOIN
    sys.partitions p ON i.object_id = p.OBJECT_ID AND i.index_id = p.index_id
INNER JOIN
    sys.allocation_units a ON p.partition_id = a.container_id
LEFT OUTER JOIN
    sys.schemas s ON t.schema_id = s.schema_id
WHERE
    t.NAME NOT LIKE 'dt%'
    AND t.is_ms_shipped = 0
    AND i.OBJECT_ID > 255
GROUP BY t.Name, s.Name, p.Rows
ORDER BY name ASC";

        //se quiser filtrar somente as que tem registro
        //AND p.rows > 1

        $result = \Db\Conn::getInstance()->query($sql, array($dbName));

        if (is_array($result))
        {
            foreach ($result as $item)
            {
                $label = $item->label;

                //prefixos organiza
                $label = str_replace('Cfc_', '', $label);
                $label = str_replace('Cna_', '', $label);
                $label = str_replace('Cpr_', '', $label);
                $label = str_replace('Crm_', '', $label);
                $label = str_replace('Cst_', '', $label);
                $label = str_replace('Ctb_', '', $label);
                $label = str_replace('Ctr_', '', $label);
                $label = str_replace('Est_', '', $label);
                $label = str_replace('Fat_', '', $label);
                $label = str_replace('Fin_', '', $label);
                $label = str_replace('Ftw_', '', $label);
                $label = str_replace('Gra_', '', $label);
                $label = str_replace('Ger_', '', $label);
                $label = str_replace('Ina_', '', $label);
                $label = str_replace('Inc_', '', $label);
                $label = str_replace('Int_', '', $label);
                $label = str_replace('Iso_', '', $label);
                $label = str_replace('Nfe_', '', $label);
                $label = str_replace('Obr_', '', $label);
                $label = str_replace('Pal_', '', $label);
                $label = str_replace('Pat_', '', $label);
                $label = str_replace('Rhu_', '', $label);
                $label = str_replace('Tra_', '', $label);
                $label = str_replace('Wor_', '', $label);

                $label = str_replace('_', ' ', $label);
                $label = str_replace('_', ' ', $label);
                $label = str_replace('_', ' ', $label);

                $label = trim(ucfirst(strtolower($label)));

                $item->label = $label;
            }
        }

        return $result;
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

        $sql = "SELECT
    t.NAME AS name,
	t.NAME AS label
FROM
    sys.tables t
INNER JOIN
    sys.indexes i ON t.OBJECT_ID = i.object_id
INNER JOIN
    sys.partitions p ON i.object_id = p.OBJECT_ID AND i.index_id = p.index_id
INNER JOIN
    sys.allocation_units a ON p.partition_id = a.container_id
LEFT OUTER JOIN
    sys.schemas s ON t.schema_id = s.schema_id
WHERE
    t.NAME NOT LIKE 'dt%'
    AND t.is_ms_shipped = 0
    AND i.OBJECT_ID > 255
GROUP BY t.Name, s.Name, p.Rows
ORDER BY name ASC;";

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
        throw new \UserException('Não implementado');
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
        $top = strlen(trim($limit)) > 0 ? 'TOP ' . $limit . ' ' : '';

        $lineEnding = $format ? "\r\n" : ' ';
        $sql = 'SELECT' . $lineEnding . $top . $columns;
        $sql .= $tables ? $lineEnding . 'FROM ' . $tables : '';
        $sql .= strlen(trim($where)) > 0 ? $lineEnding . 'WHERE ' . $where : '';
        $sql .= strlen(trim($groupBy)) > 0 ? $lineEnding . 'GROUP BY ' . $groupBy : '';
        $sql .= strlen(trim($having)) > 0 ? $lineEnding . 'HAVING ' . $having : '';
        $sql .= strlen(trim($orderBy)) > 0 ? $lineEnding . 'ORDER BY ' . $orderBy : '';
        $sql .= strlen(trim($orderWay)) > 0 ? ' ' . $orderWay : '';

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
        return " $columnName = :$columnName";
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

        return strlen(trim($table)) > 0 ? '[' . trim($table) . ']' : '';
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
        return implode(', ', $columnNames);
    }

}

if (!class_exists('\Db\Catalog'))
{

    class Catalog extends \Db\MssqlCatalog
    {

    }

}
