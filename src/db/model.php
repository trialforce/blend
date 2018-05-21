<?php

namespace Db;

use DataHandle\Config;

/**
 * Integrates the database with php.
 * Utilizes active Record concept.
 */
class Model
{

    /**
     * Ascendent order
     */
    const ORDER_ASC = 'ASC';

    /**
     * Descendent order
     */
    const ORDER_DESC = 'DESC';

    /**
     * Columns for cache, avoid large memory usage
     *
     * @var array
     */
    protected static $columnsCache;

    /**
     * Cache for primary keys
     *
     * @var array
     */
    protected static $pksCache;

    /**
     * Auto increment
     */
    const DB_AUTO_INCREMENT = 'auto_increment';

    /**
     * Return the label of model/table.
     *
     * You can overwrite to ajdust.
     *
     * @return string
     */
    public static function getLabel()
    {
        $name = self::getName();
        return \Db\Catalog::tableExists($name::getTableName())->label;
    }

    /**
     * Return the label of model in plural.
     *
     * You can overwrite to ajdust.
     *
     * @return string
     */
    public static function getLabelPlural()
    {
        $name = self::getName();
        return $name::getLabel() . 's';
    }

    /**
     * Return the name of class/table/model
     *
     * @return string
     */
    public static function getName()
    {
        //necessary because namespace
        return '\\' . get_called_class();
    }

    /**
     * Return the table name related to this model.
     * Can be overide where table name differs from class name.
     *
     * @return string
     */
    public static function getTableName()
    {
        $tableName = str_replace(array('\Model\\', '\\'), '', self::getName());
        return lcfirst($tableName);
    }

    /**
     * Return the columns indexed by name
     *
     * @return array of \Db\Column
     */
    public static function getColumns()
    {
        $name = self::getName();

        //get information from cache
        if (isset(self::$columnsCache[$name]))
        {
            return self::$columnsCache[$name];
        }

        //try to locate method in child tables
        if (method_exists($name, 'defineColumns'))
        {
            $columns = $name::defineColumns();
        }
        else
        {
            //or, get from databse
            $columns = \Db\Catalog::listColums($name::getTableName());
        }

        self::$columnsCache[$name] = $columns;

        return $columns;
    }

    /**
     * Define the columsn of Model on the fly, avoid use it
     *
     * @param array $columns
     */
    public static function setColumns($columns)
    {
        $name = self::getName();

        self::$columnsCache[$name] = $columns;
    }

    /**
     * Define one column for model
     *
     * @param \Db\Column $column
     */
    public static function setColumn(\Db\Column $column)
    {
        $name = self::getName();

        self::$columnsCache[$name][$column->getName()] = $column;
    }

    /**
     * Return all extra columns
     *
     * @return array of \Db\ExtraColumn
     */
    public static function getExtraColumns()
    {
        $name = self::getName();
        $columns = $name::getColumns();
        $extraColumns = NULL;

        foreach ($columns as $column)
        {
            if ($column instanceof \Db\ExtraColumn)
            {
                $extraColumns[$column->getName()] = $column;
            }
        }

        return $extraColumns;
    }

    /**
     * Return a column by name
     *
     * @param string $columnName
     *
     * @throws \Exception
     * @return \Db\Column
     */
    public static function getColumn($columnName)
    {
        //maximize compability
        if ($columnName instanceof \Db\Column)
        {
            return $columnName;
        }

        $columns = self::getColumns();

        if (isset($columns [$columnName]))
        {
            return $columns[$columnName];
        }

        return NULL;
    }

    /**
     * Verifica se coluna existe no modelo
     *
     * @return boolean
     */
    public static function columnExist($column)
    {
        foreach (self::getColumns() as $col)
        {
            if ($col->getName() == $column)
            {
                return TRUE;
            }
        }

        return FALSE;
    }

    /**
     * Retorna as chaves primária da tabela
     *
     * @return array de \Db\Column
     */
    public static function getPrimaryKeys()
    {
        $pk = array();
        $name = self::getName();

        //make cache for pks read
        if (isset(self::$pksCache[$name]))
        {
            return self::$pksCache[$name];
        }

        $columns = $name::getColumns();

        foreach ($columns as $column)
        {
            if ($column instanceof \Db\Column && $column->isPrimaryKey())
            {
                $pk[$column->getName()] = $column;
            }
        }

        self::$pksCache[$name] = $pk;

        return $pk;
    }

    /**
     * Return the first primary key
     *
     * @throws \Exception
     * @return \Db\Column
     */
    public static function getPrimaryKey()
    {
        $pksV = array_values(self::getPrimaryKeys());

        return $pksV[0];
    }

    /**
     * Return an array with name => value of passed columns.
     * If no column is passed, get the default column from model
     *
     * @return array
     */
    public function getColumnValues($columns = NULL, $avoidPk = TRUE)
    {
        $name = self::getName();
        $columnNames = !is_null($columns) ? $columns : $name::getColumns();

        if (!is_array($columnNames))
        {
            throw new Exception('Sem colunas para obter valores!');
        }

        foreach ($columnNames as $columnName => $column)
        {
            $value = $this->getValueDb($columnName);
            $check = $avoidPk ? ($column->isPrimaryKey() && $value == '' ) : $value === '';

            if ($column instanceof \Db\SearchColumn || $column instanceof \Db\ExtraColumn || $check)
            {
                continue;
            }

            $columnValues[$columnName] = $value;
        }

        return $columnValues;
    }

    /**
     * Garante que a variável esteja num array
     * caso não seja, e limpa o desnecessário.
     *
     * @param mixed $var
     * @return array
     */
    protected static function toArray($var)
    {
        if (!is_array($var))
        {
            $var = array($var);
        }

        $var = array_filter($var);

        return $var;
    }

    /**
     * Monta a parte WHERE do sql baseado em filtros \Db\Cond
     *
     * @param array $filters de \Db\Cond
     * @return \stdClass
     * @throws \Exception
     */
    public static function getWhereFromFilters($filters)
    {
        $args = array();
        $argsHaving = array();
        $sql = '';
        $having = '';
        $filters = \Db\Model::toArray($filters);
        $count = 0;
        $countHaving = 0;

        //monta todos os WHERE's baseado nos arrays de filtros
        if (count($filters) > 0)
        {
            foreach ($filters as $filter)
            {
                if (!$filter instanceof \Db\Cond || is_null($filter))
                {
                    continue;
                }

                if ($filter->getType() === \Db\Cond::TYPE_HAVING)
                {
                    $having .= $filter->getWhere($countHaving === 0);
                    $countHaving++;

                    if (!is_null($filter->getValue()))
                    {
                        $argsHaving = array_merge($argsHaving, $filter->getValue());
                    }
                }
                else
                {
                    $sql .= $filter->getWhere($count === 0);
                    $count++;

                    if (!is_null($filter->getValue()))
                    {
                        $args = array_merge($args, $filter->getValue());
                    }
                }
            }
        }

        //mount a simple object for return
        $result = new \stdClass();
        $result->sql = $sql;
        $result->having = $having;
        $result->args = array_merge($args, $argsHaving);

        return $result;
    }

    /**
     * Monta as colunas que irão no sql de busca
     *
     * @param array $columns
     * @return string
     */
    protected static function getColumnsForFind($columns)
    {
        $result = array();

        foreach ($columns as $column)
        {
            $name = self::getName();
            $tableName = $name::getTableName();
            $column->setTableName($tableName);
            $line = $column->getSql();

            if (is_array($line))
            {
                $result = array_merge($line, $result);
            }
        }

        return $result;
    }

    /**
     * Execute a search in database and return a list
     *
     * @param \Db\Cond $filters
     * @param int $limit
     * @param int $offset
     * @param string $orderBy
     * @param string $orderWay
     *
     * @return array
     */
    public static function find($filters = array(), $limit = NULL, $offset = NULL, $orderBy = NULL, $orderWay = NULL, $returnType = NULL)
    {
        $name = self::getName();
        return $name::search(NULL, $filters, $limit, $offset, $orderBy, $orderWay, $returnType);
    }

    /**
     * Obtem o find() ordenado pelo atributo ID.
     *
     * @return array
     */
    public static function findAllIndexed()
    {
        $indexedRows = self::orderArrayByProperty(self::find(), 'id');
        $list = array();

        foreach ((array) $indexedRows as $key => $row)
        {
            $list[$key] = $row->populateIndexed();
        }

        return $list;
    }

    /**
     *
     * @param type $columns
     * @param \Db\Cond $filters
     * @param type $limit
     * @param type $offset
     * @param type $orderBy
     * @param type $orderWay
     * @param type $returnType
     *
     * @return array
     */
    public static function search($columns = NULL, $filters = array(), $limit = NULL, $offset = NULL, $orderBy = NULL, $orderWay = NULL, $returnType = NULL)
    {
        if (is_string($filters))
        {
            $filters = new \Db\Cond($filters);
        }

        $name = self::getName();

        if (!$columns)
        {
            $columns = $name::getColumns();
        }

        //mount group by
        $groupBy = NULL;
        $grpIndex = count($columns);

        foreach ($columns as $column)
        {
            if ($column instanceof \Db\GroupColumn && ($column->getAgg() == \Db\GroupColumn::METHOD_GROUP || !$column->getAgg()))
            {
                $groupBy[] = $grpIndex;
            }

            $grpIndex--;
        }

        if (is_array($groupBy))
        {
            $groupBy = implode(', ', $groupBy);
        }

        $columnNameSql = self::getColumnsForFind($columns);
        $where = self::getWhereFromFilters($filters);

		$catalog = $name::getCatalogClass();
		
        //if has ASC or DESC in order BY don't treat then, let it be
        if (!( stripos($orderBy, 'ASC') > 0 || stripos($orderBy, 'DESC') > 0))
        {
            $orderBy = implode(',', $catalog::parseTableNameForQuery(explode(',', $orderBy)));
        }

        $table = $catalog::parseTableNameForQuery($name::getTableName());
        $sql = $catalog::mountSelect($table, $catalog::implodeColumnNames($columnNameSql), $where->sql, $limit, $offset, $groupBy, $where->having, $orderBy, $orderWay);
		
        $returnType = is_null($returnType) ? $name : $returnType;
        $instanceOf = in_array('Db\SqlCache', class_implements($name));

        if (Config::get('dbSsqlCache') && $instanceOf)
        {
            $stringCache = $sql . implode('', $where->args) . $returnType;
            $result = \Db\CacheSql::get($name::getTableName(), $stringCache);

            if (!$result)
            {
                $result = $name::getConn()->query($sql, $where->args, $returnType);
                \Db\CacheSql::set($name::getTableName(), $stringCache, $result);
            }
        }
        else
        {
            $result = $name::getConn()->query($sql, $where->args, $returnType);
        }

        return $result;
    }
	
	public static function getCatalogClass()
	{
		$name = self::getName();;
		$conn = $name::getConnInfo();

		if ( $conn->getType() == \Db\ConnInfo::TYPE_MYSQL )
		{
			return '\Db\MysqlCatalog';
		}
		else if ( $conn->getType() == \Db\ConnInfo::TYPE_POSTGRES )
		{
			return '\Db\PgsqlCatalog';
		}
		else if ( $conn->getType() == \Db\ConnInfo::TYPE_MSSQL )
		{
			return '\Db\MssqlCatalog';
		}
		
		return '\Db\MysqlCatalog';
	}

    /**
     * Execute a search in database and return a list
     *
     * Function used by reference field
     *
     * @param \Db\Cond $filters
     * @param int $limit
     * @param int $offset
     * @param string $orderBy
     * @param string $orderWay
     *
     * @return array
     */
    public static function findForReference($filters = array(), $limit = NULL, $offset = NULL, $orderBy = NULL, $orderWay = NULL)
    {
        $name = self::getName();

        $columns = array_values($name::getColumns());
        $firstColumnName = $columns[1]->getName();

        $orderBy = $orderBy ? $orderBy : $firstColumnName;
        $orderWay = $orderWay ? $orderWay : 'ASC';

        return $name::find($filters, $limit, $offset, $orderBy, $orderWay);
    }

    /**
     * Faz a contagem
     *
     * @param type $filters
     *
     * @return int
     */
    public static function count($filters = array(), $value = '*')
    {
        $name = self::getName();

        return $name::aggregation($filters, 'count(' . $value . ')');
    }

    public static function aggregation($filters = array(), $aggregation = 'count(*)', $forceExternalSelect = FALSE)
    {
        $name = self::getName();
        $where = self::getWhereFromFilters($filters);
        $hasHaving = strlen($where->having) > 0;
        $columnNameSql = self::getColumnsForFind($name::getColumns());
        $columnsString = \Db\Catalog::implodeColumnNames($columnNameSql);

        if ($hasHaving || $forceExternalSelect)
        {
            $columns = $columnsString;
        }
        else
        {
            $columns = $aggregation . ' as aggregation ,' . $columnsString;
        }

        $tableName = \Db\Catalog::parseTableNameForQuery($name::getTableName());
        $groupBy = NULL;

        if (self::getConnInfo()->getType() == \Db\ConnInfo::TYPE_POSTGRES)
        {
            $groupBy = $columnsString;
        }

        $sql = \Db\Catalog::mountSelect($tableName, $columns, $where->sql, NULL, NULL, $groupBy, $where->having);

        if ($hasHaving || $forceExternalSelect)
        {
            $sql = 'SELECT ' . $aggregation . ' as aggregation FROM ( ' . $sql . ') AS ag';
        }

        $result = $name::getConn()->findOne($sql, $where->args);

        return isset($result) ? $result->aggregation : 0;
    }

    /**
     * Obtem um registro pelo id
     *
     * @param string $id
     * @return \Db\Model
     */
    public static function findOneByPk($id)
    {
        //avoid sql if not id passed
        //support id = 0
        if (!$id && $id !== '0')
        {
            return NULL;
        }

        $name = self::getName();

        //segurança para compatibilidade com código antigo
        if (!is_array($id))
        {
            $id = array($id);
        }

        $pks = $name::getPrimaryKeys();
        $i = 0;

        $filters = array();
		
		$catalog = $name::getCatalogClass();

        foreach ($pks as $pk)
        {
            $tableName = $catalog::parseTableNameForQuery($name::getTableName());
            $pkName = $catalog::parseTableNameForQuery($pk->getName());
            $pkName = $tableName . '.' . $pkName;

            if (isset($id[$i]))
            {
                $filters[] = new \Db\Cond($pkName . ' = ?', $id[$i]);
            }

            $i++;
        }

        //procura por um só registro
        return $name::findOne($filters);
    }

    /**
     * Find one object by pk or create
     *
     * If not find create anotger
     *
     * @param type $id
     * @return \Db\name
     */
    public static function findOneByPkOrCreate($id)
    {
        $name = self::getName();

        if (!$id)
        {
            $obj = new $name();
        }
        else
        {
            $obj = $name::findOneByPk($id);

            if (!$obj)
            {
                $obj = new $name();
            }
        }

        return $obj;
    }

    /**
     * Retorna um único registro do banco.
     *
     * É um atalho para find
     *
     * @param array $filters
     * @return null
     */
    public static function findOne($filters = array())
    {
        $name = self::getName();
        $result = $name::find($filters, 1);

        if (isset($result[0]))
        {
            return $result[0];
        }

        return NULL;
    }

    /**
     * Retorna um único registro do banco.
     *
     * É um atalho para find
     *
     * @param array $filters
     * @return null
     */
    public static function findOneOrCreate($filters = array())
    {
        $name = self::getName();
        $result = $name::find($filters, 1);

        if (isset($result[0]))
        {
            return $result[0];
        }

        return new $name();
    }

    /**
     * Monta filtros para busca automágica alá Google
     *
     * @param string $filter
     * @param array $extraFilters
     *
     * @return array
     */
    public static function smartFilters($filter = NULL, $extraFilters = array(), $columns = NULL)
    {
        $modelClass = self::getName();
        $smartFilters = new SmartFilter($modelClass, $columns, $filter);
        $filters = $smartFilters->createFilters();

        return array_merge($filters, self::toArray($extraFilters));
    }

    /**
     * Executa uma busca automágica alá google
     *
     * @param array $filter
     * @param array $extraFilters
     * @param int $limit
     * @param int $offset
     * @param string $orderBy
     * @param string $orderWay
     *
     * @return array
     */
    public static function smartFind($filter = NULL, $extraFilters = array(), $limit = NULL, $offset = NULL, $orderBy = NULL, $orderWay = NULL, $returnType = NULL)
    {
        $name = self::getName();
        return $name::find(self::smartFilters($filter, $extraFilters, $name::getColumns()), $limit, $offset, $orderBy, $orderWay, $returnType);
    }

    /**
     * Faz a inserção de um registro
     *
     * @return int
     */
    public function insert($columns = NULL)
    {
        $name = self::getName();
        $columnValues = $this->getColumnValues($columns, FALSE);
        $columnNames = \Db\Catalog::implodeColumnNames(array_keys($columnValues));
        $columnNameSql = ':' . implode(', :', array_keys($columnValues));

        $tableName = \Db\Catalog::parseTableNameForQuery($name::getTableName());

        $sql = \Db\Catalog::mountInsert($tableName, $columnNames, $columnNameSql, $this->getPrimaryKey());
        $pk = $this->getPrimaryKey();
        $id = $pk->getName();

        //postgres faz query e já retorna id
        if (self::getConnInfo()->getType() == \Db\ConnInfo::TYPE_POSTGRES)
        {
            $ok = $name::getConn()->query($sql, $columnValues);

            if ($pk->isAutoPrimaryKey())
            {
                $this->$id = $ok[0]->{$id};
            }
        }
        else //mysql é necessário obter em um novo select
        {
            $conn = $name::getConn();
            $ok = $conn->execute($sql, $columnValues);

            //somente suporta popular com chave única
            if ($pk->isAutoPrimaryKey())
            {
                $this->$id = $conn->lastInsertId();
            }
        }

        if (Config::get('dbSsqlCache') && $this instanceof \Db\SqlCache)
        {
            \Db\CacheSql::clearForTable($name::getTableName());
        }

        return $ok;
    }

    /**
     * Update current object
     *
     * @return int quantity of updated register
     */
    public function update($columns = NULL)
    {
        $name = self::getName();
        $columnValues = $this->getColumnValues($columns);
		$catalog = $name::getCatalogClass();

        foreach ($columnValues as $columnName => $value)
        {
            //value is not used in this case
            $value = null;
            $sqlColumns[] = $catalog::parseColumnNameForQuery($columnName);
        }

        $sqlWhere = array();
        $pk = $this->getPrimaryKeys();

        foreach ($pk as $pkName => $pk)
        {
            $sqlWhere[] = $catalog::parseColumnNameForQuery($pkName);
        }

        $tableName =$catalog::parseTableNameForQuery($name::getTableName());
        $sql = $catalog::mountUpdate($tableName, implode(', ', $sqlColumns), implode(' AND ', $sqlWhere));
		
        if (Config::get('dbSsqlCache') && $this instanceof \Db\SqlCache)
        {
            \Db\CacheSql::clearForTable($name::getTableName());
        }

        return $name::getConn()->execute($sql, $columnValues);
    }

    /**
     * Salva, faz a persistencia.
     * Executa inserção ou atualização de acordo com a necessidade.
     *
     * @return integer
     */
    public function save($columns = NULL)
    {
        $pks = $this->getPrimaryKeys();
        $update = FALSE;

        //detecta se precisa fazer update considerando se existem valores de chave primária
        foreach ($pks as $pk)
        {
            $pkName = $pk->getName();
            $pkValue = $this->getValue($pkName);

            if ($pkValue || $pkValue === 0 || $pkValue === '0')
            {
                $update = TRUE;
            }
        }

        if (!$this->getPrimaryKey()->isAutoPrimaryKey())
        {
            $update = TRUE;
        }

        if ($update)
        {
            $ok = $this->update($columns);

            //in case that d'ont exist
            if (!$this->getPrimaryKey()->isAutoPrimaryKey())
            {
                $ret = \Db\Conn::getLastRet();
                $affected = $ret->rowCount();

                if (!$affected)
                {
                    $ok = $this->insert($columns);
                }
                else
                {
                    return $ok;
                }
            }

            return $ok;
        }
        else
        {
            return $this->insert($columns);
        }
    }

    public function saveChain()
    {
        $this->save();

        return $this->populateIndexed();
    }

    /**
     *
     * @return array
     */
    protected function populateIndexed()
    {
        return $this->getArray();
    }

    /**
     * Make the validation of data in model
     *
     * @return array
     */
    public function validate()
    {
        $columns = $this->getColumns();
        $error = NULL;

        //passa pelas colunas chamando a validação das colunas
        foreach ($columns as $column)
        {
            if (!$column instanceof \Db\SearchColumn)
            {
                $columnName = $column->getName();
                $value = $this->getValue($columnName);

                //pega o valor do banco para validar
                if ($value instanceof \Type\Generic)
                {
                    $value = $value->toDb();
                }

                $result = $column->validate($value);

                if (count($result) > 0)
                {
                    //avoid duplicate messages
                    $error[$columnName] = array_unique($result);
                }
            }
        }

        return $error;
    }

    /**
     * Remove the current object from databse
     *
     * @return int
     */
    public function delete()
    {
        $name = $this->getName();
        $pks = $this->getPrimaryKeys();
        $where = null;

        if (is_array($pks) && count($pks) > 0)
        {
            foreach ($pks as $pk)
            {
                $pkName = $pk->getName();
                $where[] = " $pkName = :$pkName ";
                $args[$pkName] = $this->$pkName;
            }
        }
        else
        {
            throw new \Exception('Tabela sem chave primária, impossível remover!');
        }

        $tableName = \Db\Catalog::parseTableNameForQuery($name::getTableName());

        $sql = \Db\Catalog::mountDelete($tableName, implode(' AND ', $where));

        if (Config::get('dbSsqlCache') && $this instanceof \Db\SqlCache)
        {
            \Db\CacheSql::clearForTable($tableName);
        }

        return $name::getConn()->execute($sql, $args);
    }

    /**
     * Delete com filtros
     *
     * @param array $filters
     * @return array
     */
    public static function remove($filters)
    {
        $name = self::getName();
        $where = $name::getWhereFromFilters($filters);
        $sql = \Db\Catalog::mountDelete($name::getTableName(), $where->sql);

        if (Config::get('dbSsqlCache') && $this instanceof \Db\SqlCache)
        {
            \Db\CacheSql::clearForTable($name::getTableName());
        }

        return $name::getConn()->execute($sql, $where->args);
    }

    /**
     * Remove passando um array de ids
     *
     * @param array $ids
     * @return array
     */
    public static function removeInId(array $ids)
    {
        $name = self::getName();
        $filters[] = new \Db\Cond($name::getPrimaryKey() . ' IN (' . implode(',', $ids) . ')');
        return $name::remove($filters);
    }

    /**
     * Method used to auto mount selects
     *
     * @return string
     */
    public function getOptionValue()
    {
        $pk = self::getPrimaryKey();
        return $this->getValue($pk->getName());
    }

    /**
     * Method used to auto mount selects (label)
     *
     * @return string
     */
    public function getOptionLabel()
    {
        $name = self::getName();
        $columns = array_values($name::getColumns());
        //jump pk
        $firstColumnName = $columns[1]->getName();
        return $this->getValue($firstColumnName);
    }

    public function getTitleLabel()
    {
        return '';
    }

    /**
     * Return connection id
     *
     * @return string
     */
    public static function getConnId()
    {
        return 'default';
    }

    /**
     * Return conn info
     *
     * @return \Db\ConnInfo
     */
    public static function getConnInfo()
    {
        $name = self::getName();
        return \Db\Conn::getConnInfo($name::getConnId());
    }

    /**
     * Return the connection of current model
     *
     * @return \Db\Conn
     */
    public static function getConn()
    {
        $name = self::getName();
        return \Db\Conn::getInstance($name::getConnId());
    }

    /**
     * Return a value from model, detect set/get or public variable
     *
     * @param string $property
     *
     * @return string
     */
    public function getValue($property)
    {
        $column = $this->getColumn($property);

        if ($column)
        {
            $property = $column->getProperty();
        }

        $methodName = 'get' . $property;

        if (method_exists($this, $methodName))
        {
            return $this->$methodName();
        }
        else
        {
            return $this->$property;
        }

        return NULL;
    }

    /**
     * Return a value from model, detect set/get or public variable
     *
     * Ready for database
     *
     * @param string $property
     * @return string
     */
    public function getValueDb($property)
    {
        $value = $this->getValue($property);

        if ($value instanceof \Type\Generic)
        {
            $value = $value->toDb();
        }

        return $value;
    }

    /**
     * Define a value in current model. Supports
     *
     * Support get/set or public variables, according to the situation.
     *
     * @param string $property
     * @param mixed $value
     * @return \Db\Model
     */
    public function setValue($property, $value)
    {
        $column = $this->getColumn($property);

        if ($column)
        {
            $property = $column->getProperty();
        }

        $methodName = 'set' . $property;

        if (method_exists($this, $methodName))
        {
            $this->$methodName($value);
        }
        else
        {
            $this->$property = $value;
        }

        return $this;
    }

    /**
     * Define the data from request in the model
     *
     * @param \DataHandle $request
     * @return \Db\Model
     */
    public function setData(\DataHandle\DataHandle $request, $overwrite = TRUE)
    {
        $name = self::getName();
        $columns = $name::getColumns();

        //passes through the columns of model
        foreach ($columns as $column)
        {
            //disregards case of automatic primary key, search colum
            if ($column instanceof \Db\Column)
            {
                $property = $column->getProperty();
                $value = $request->getvar($property);

                if ($overwrite || ($value || $value == '0' || $value == 0))
                {
                    $this->setValue($property, $value);
                }
            }
        }

        return $this;
    }

    /**
     * Return a data of the object
     *
     * @return \DataHandle\DataHandle
     */
    public function getData()
    {
        return new \DataHandle\DataHandle($this->getArray());
    }

    /**
     * Convert a model to an array
     *
     * @return array
     */
    public function getArray()
    {
		$name = self::getName();
		$columns = $name::getColumns();
        $temp = (array)($this);
        $array = array();

        foreach ($temp as $k => $v)
        {
            $k = preg_match('/^\x00(?:.*?)\x00(.+)/', $k, $matches) ? $matches[1] : $k;
			
            if ($v instanceof \Type\Generic)
            {
                $v = $v->toDb();
            }
			
			$column = $columns[$k];
			
			//add suport to decimal values
			if ($column && $column->getType() == \Db\Column::TYPE_DECIMAL)
			{
				$v = \Type\Decimal::get($v)->toDb();
			}
			
            $array[$k] = $v;
        }

        return $array;
    }

    /**
     * Return a json strin representation of this model
     *
     * @return string
     */
    public function getJson()
    {
        return json_encode($this->getArray());
    }

    /**
     * Order and array of models by a passed property
     *
     * @param array $array
     * @param string $property
     * @return array
     */
    public static function orderArrayByProperty($array, $property)
    {
        $result = NULL;

        if (is_array($array))
        {
            foreach ($array as $model)
            {
                $index = $model->getValue($property);
                $result[$index] = $model;
            }
        }

        return $result;
    }

    /**
     * Supports the search for variables in the model. Even if they are not declared.
     * In other words variables declared without support models, but avoids error when using PHP_STRICT.
     *
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        if (isset($this->$name))
        {
            return $this->$name;
        }

        return NULL;
    }

    /**
     * Return the name of the class
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getName();
    }

}

/**
 * Implements this interface to make your model use sqlCache
 */
interface SqlCache
{

}
