<?php

namespace Db;

/**
 * Integrates the database with php.
 * Utilizes active Record concept.
 */
class Model implements \JsonSerializable
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
     * Auto increment constant
     */
    const DB_AUTO_INCREMENT = 'auto_increment';

    /**
     * Return the name of class/table/model
     *
     * @return string
     */
    public static function getName()
    {
        return '\\' . get_called_class();
    }

    /**
     * Return the table name related to this model.
     * Can be override where table name differs from class name.
     *
     * @return string
     */
    public static function getTableName()
    {
        $name = self::getName();
        $tableName = str_replace(array('\Model\\', '\\'), '', $name);
        return $name::getTablePrefix() . lcfirst($tableName);
    }

    /**
     * Return the table prefix related to this model
     * Normally defined by dbprefix-connection name in config.php
     *
     * @return string
     */
    public static function getTablePrefix()
    {
        $name = self::getName();
        $prefixName = 'dbprefix-' . $name::getConnId();
        return \DataHandle\Config::getDefault($prefixName, '');
    }

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
        $catalog = $name::getCatalogClass();
        return $catalog::tableExists($name::getTableName())->label;
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
     * Return connection id
     *
     * @return string
     */
    public static function getConnId()
    {
        return 'default';
    }

    /**
     * Return conn info of current model
     *
     * @return \Db\ConnInfo
     */
    public static function getConnInfo()
    {
        $name = self::getName();
        return \Db\Conn::getConnInfo($name::getConnId());
    }

    /**
     * Get the catalog class name of this model
     *
     * @return string
     */
    public static function getCatalogClass()
    {
        $name = self::getName();
        $connInfo = $name::getConnInfo();
        return $connInfo->getCatalogClass();
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
     * Return the columns indexed by name
     *
     * @return array of \Db\Column\Column
     */
    public static function getColumns()
    {
        return \Db\Column\Collection::getForModel(self::getName());
    }

    /**
     * Define the columsn of Model on the fly, avoid use it
     *
     * @param array $columns
     */
    public static function setColumns($columns)
    {
        \Db\Column\Collection::setForModel(self::getName(), $columns);
    }

    /**
     * Define one column for model
     *
     * @param \Db\Column\Column $column
     */
    public static function setColumn(\Db\Column\Column $column)
    {
        \Db\Column\Collection::getForModel(self::getName())->setColumn($column);
    }

    /**
     * Return a column by name
     *
     * @param string $columnName
     *
     * @throws \Exception
     * @return \Db\Column\Column
     */
    public static function getColumn($columnName)
    {
        return \Db\Column\Collection::getForModel(self::getName())->getColumn($columnName);
    }

    /**
     * Verifica se coluna existe no modelo
     *
     * @return boolean
     */
    public static function columnExist($columnName)
    {
        return \Db\Column\Collection::getForModel(self::getName())->columnExist($columnName);
    }

    /**
     * Retorna as chaves primária da tabela
     *
     * @return array de \Db\Column\Column
     */
    public static function getPrimaryKeys()
    {
        return \Db\Column\Collection::getForModel(self::getName())->getPrimaryKeys();
    }

    /**
     * Return the first primary key
     *
     * @throws \Exception
     * @return \Db\Column\Column
     */
    public static function getPrimaryKey()
    {
        return \Db\Column\Collection::getForModel(self::getName())->getPrimaryKey();
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

        if (!isIterable($columnNames))
        {
            throw new \Exception('Sem colunas para obter valores!');
        }

        foreach ($columnNames as $columnName => $column)
        {
            $value = $this->getValueDb($columnName);
            $check = $avoidPk ? ($column->isPrimaryKey() && $value == '' ) : $value === '';

            if ($column instanceof \Db\Column\Search || $check)
            {
                continue;
            }

            $columnValues[$columnName] = $value;
        }

        return $columnValues;
    }

    /**
     * Parse the filters change to the search column sql when necessary
     *
     * @param array $filters array of filters
     * @param string $className model class name
     * @return array array of filters
     */
    public static function parseSearchColumnWhere($filters, $className)
    {
        if (!$className)
        {
            return $filters;
        }

        if (!is_array($filters))
        {
            $filters = array($filters);
        }

        foreach ($filters as $filter)
        {
            if (!$filter)
            {
                continue;
            }

            $filter instanceof \Db\Where;

            if (method_exists($filter, 'getFilter'))
            {
                //remove Mysql crap escape character, sometimes we can get it here (from \Db\QueryBuilder)
                $sqlFilter = str_replace('`', '', $filter->getFilter());
                $column = $className::getColumn($sqlFilter);

                if ($column && $column instanceof \Db\Column\Search)
                {
                    $searchSql = $column->getSql(false);
                    $filter->setFilter($searchSql[0]);
                }
            }
        }

        return $filters;
    }

    /**
     * Return the Query builder for this Model
     * @return \Db\QueryBuilder
     */
    public static function query($tableNameInColumns = false)
    {
        $name = self::getName();
        $tableName = $name::getTableName();

        $queryBuilder = new \Db\QueryBuilder($name::getTableName(), $name::getConnId());
        $queryBuilder->setCatalogClass($name::getCatalogClass());

        $columns = \Db\Column\Collection::getForModel($name);
        $result = array();

        foreach ($columns as $column)
        {
            $line = $column->getSql();

            if ($tableNameInColumns && !$column instanceof \Db\Column\Search)
            {
                $line[0] = $tableName . '.' . $line[0];
            }

            if (is_array($line))
            {
                $result = array_merge($line, $result);
            }
        }

        $queryBuilder->setColumns(array_reverse($result));
        $queryBuilder->setModelName(get_called_class());

        return $queryBuilder;
    }

    /**
     * Query the current query builder and apply an initial where
     *
     * @param string $columnName
     * @param string $param
     * @param string $value
     * @return \Db\QueryBuilder
     */
    public static function where($columnName, $param = NULL, $value = NULL)
    {
        $name = self::getName();
        $queryBuilder = $name::query();
        return $queryBuilder->where($columnName, $param, $value);
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
     * Execute a search in database ans return a list
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
        $columns = \Db\Column\Collection::chooseForModel($name, $columns);

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

        $columnNameSql = $columns->getSqlNamesForFind();
        $where = \Db\Criteria::createCriteria(\Db\Model::parseSearchColumnWhere($filters, $name));
        $catalog = $name::getCatalogClass();

        //if has ASC or DESC in order BY don't treat then, let it be
        if (!( stripos($orderBy, 'ASC') > 0 || stripos($orderBy, 'DESC') > 0))
        {
            $orderBy = implode(',', $catalog::parseTableNameForQuery(explode(',', $orderBy)));
        }

        $table = $catalog::parseTableNameForQuery($name::getTableName());
        $sql = $catalog::mountSelect($table, $catalog::implodeColumnNames($columnNameSql), $where->getSql(), $limit, $offset, $groupBy, NULL, $orderBy, $orderWay);

        $returnType = is_null($returnType) ? $name : $returnType;
        $result = $name::getConn()->query($sql, $where->getArgs(), $returnType);

        return $result;
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
        $orderBy = $orderBy ? $orderBy : \Db\Column\Collection::getForModel($name)->getColumnAtPosition(0);
        $orderWay = $orderWay ? $orderWay : 'ASC';

        return $name::find($filters, $limit, $offset, $orderBy, $orderWay);
    }

    /**
     * Make a count on database
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

    public static function aggregations($filters = array(), $aggregations, $forceExternalSelect = FALSE, $columns = NULL)
    {
        $name = self::getName();
        $where = self::getWhereFromFilters($filters);
        $columns = \Db\Column\Collection::chooseForModel($name, $columns);
        $catalog = $name::getCatalogClass();
        $tableName = $catalog::parseTableNameForQuery($name::getTableName());

        $columnsAggregation = NULL;

        foreach ($aggregations as $columnName => $query)
        {
            $columnsAggregation[] = $query . ' as aggregation' . $columnName;
        }

        $columns = implode(',', $columnsAggregation);

        $sql = $catalog::mountSelect($tableName, $columns, $where->getSql(), NULL, NULL, NULL, NULL);

        if ($forceExternalSelect)
        {
            $sql = 'SELECT ' . implode(',', $columnsAggregation) . ' FROM ( ' . $sql . ') AS ag';
        }

        $result = $name::getConn()->findOne($sql, $where->getArgs());

        return isset($result) ? $result : 0;
    }

    public static function aggregation($filters = array(), $aggregation = 'count(*)', $forceExternalSelect = FALSE, $columns = NULL)
    {
        $name = self::getName();
        $result = $name::aggregations($filters, ['aggregation' => $aggregation], $forceExternalSelect, $columns);

        return isset($result) ? $result->aggregationaggregation : 0;
    }

    /**
     * Find one register by its Id
     *
     * @param string $id
     * @param bool $useCache
     * @return \Db\Model
     */
    public static function findOneByPk($id, $useCache = FALSE)
    {
        static $findOneCache;
        $cacheKey = null;

        //avoid sql if not id passed, support id = 0
        if (!$id && $id !== '0')
        {
            return NULL;
        }

        //security to support legacy code
        $ids = is_array($id) ? $id : array($id);
        $name = self::getName();
        $pks = $name::getPrimaryKeys();
        $catalog = $name::getCatalogClass();
        $filters = array();
        $i = 0;
        $tableName = $catalog::parseTableNameForQuery($name::getTableName());
        $cacheKey = null;

        //mount filters
        foreach ($pks as $pk)
        {
            $pkName = $catalog::parseTableNameForQuery($pk->getName());
            $pkName = $tableName . '.' . $pkName;

            if (isset($ids[$i]))
            {
                $filters[] = new \Db\Cond($pkName . ' = ?', $ids[$i]);
                $cacheKey .= $ids[$i];
            }

            $i++;
        }

        //read from cache
        if ($useCache && $cacheKey)
        {
            if (isset($findOneCache[$tableName]) && isset($findOneCache[$tableName][$cacheKey]))
            {
                return $findOneCache[$tableName][$cacheKey];
            }
        }

        //find one register
        $result = $name::findOne($filters);

        //put on cache
        if ($useCache && $cacheKey)
        {
            $findOneCache[$tableName][$cacheKey] = $result;
        }

        return $result;
    }

    /**
     * Find one object by pk or create
     *
     * If not find create anotger
     *
     * @param type $id
     * @return \Db\name
     */
    public static function findOneByPkOrCreate($id = null)
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
     * Mount filters to automatic search like Google
     *
     * @param string $filter the "google" query string question
     * @param array $extraFilters extra filter to be added to the filters
     * @param array $columns columns to be considered in smartFilter
     *
     * @return array
     */
    public static function smartFilters($filter = NULL, $extraFilters = array(), $columns = NULL)
    {
        $modelClass = self::getName();
        $smartFilters = new SmartFilter($modelClass, $columns, $filter);
        $filters = $smartFilters->createFilters();

        $extraFilters = is_array($extraFilters) ? array_filter($extraFilters) : array($extraFilters);

        return array_merge($filters, $extraFilters);
    }

    /**
     * Execute a automatic search like Google
     *
     * @deprecated since version 2019-09-20
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
        return $name::find($name::smartFilters($filter, $extraFilters, \Db\Column\Collection::getForModel($name)), $limit, $offset, $orderBy, $orderWay, $returnType);
    }

    /**
     * Delete com filtros
     *
     * @param array $filters
     * @return array
     */
    public static function remove($filters = null)
    {
        $name = self::getName();
        $where = $name::getWhereFromFilters($filters);
        $catalog = $name::getCatalogClass();
        $sql = $catalog::mountDelete($name::getTableName(), $where->getSql());

        return $name::getConn()->execute($sql, $where->getArgs());
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
     * Make a database insert
     *
     * @return int
     */
    public function insert($columns = NULL)
    {
        $name = self::getName();
        $columnValues = $this->getColumnValues($columns, FALSE);
        $catalog = $name::getCatalogClass();
        $columnNames = $catalog ::implodeColumnNames(array_keys($columnValues));
        $columnNameSql = ':' . implode(', :', array_keys($columnValues));

        $tableName = $catalog ::parseTableNameForQuery($name::getTableName());

        $pk = $this->getPrimaryKey();
        $sql = $catalog ::mountInsert($tableName, $columnNames, $columnNameSql, $pk);
        $id = $pk ? $pk->getName() : '';

        //postgres faz query e já retorna id
        if (self::getConnInfo()->getType() == \Db\ConnInfo::TYPE_POSTGRES)
        {
            $ok = $name::getConn()->query($sql, $columnValues);

            if ($pk && $pk->isAutoPrimaryKey())
            {
                $this->$id = $ok[0]->{$id};
            }
        }
        else //mysql is necessary to call a method
        {
            $conn = $name::getConn();
            $ok = $conn->execute($sql, $columnValues);

            //somente suporta popular com chave única
            if ($pk && $pk->isAutoPrimaryKey())
            {
                $this->$id = $conn->lastInsertId();
            }
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

        $tableName = $catalog::parseTableNameForQuery($name::getTableName());
        $sql = $catalog::mountUpdate($tableName, implode(', ', $sqlColumns), implode(' AND ', $sqlWhere));

        return $name::getConn()->execute($sql, $columnValues);
    }

    /**
     * Save the model in database, make persistence.
     * Call insert or update according situation
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

        $pk = $this->getPrimaryKey();
        $isPk = $pk ? $pk->isAutoPrimaryKey() : false;

        if (!$isPk)
        {
            $update = TRUE;
        }

        if ($update)
        {
            $ok = $this->update($columns);

            //in case that d'ont exist
            if (!$isPk)
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

    /**
     * Duplicate current object in database
     *
     * @return int
     */
    public function duplicate()
    {
        $this->setId(null);
        return $this->insert();
    }

    /**
     * Make the validation of data in model
     *
     * @return array
     */
    public function validate()
    {
        $columns = \Db\Column\Collection::getForModel(self::getName());
        $error = NULL;

        //pass trough columns calling each validate
        foreach ($columns as $column)
        {
            if ($column instanceof \Db\Column\Search)
            {
                continue;
            }

            //don't validate auto increment columns
            if ($column->getExtra() == \Db\Column\Column::EXTRA_AUTO_INCREMENT)
            {
                continue;
            }

            $columnName = $column->getName();
            $value = $this->getValue($columnName);

            //get the databse value, if necessary
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

        $catalog = $name::getCatalogClass();
        $tableName = $catalog::parseTableNameForQuery($name::getTableName());
        $sql = $catalog::mountDelete($tableName, implode(' AND ', $where));

        return $name::getConn()->execute($sql, $args);
    }

    /**
     * Method used to auto mount selects
     *
     * @return string
     */
    public function getOptionValue()
    {
        $pk = \Db\Column\Collection::getForModel(self::getName())->getPrimaryKey();
        return $this->getValue($pk->getName());
    }

    /**
     * Method used to auto mount selects (label)
     * when using foreign key
     *
     * @return string
     */
    public function getOptionLabel()
    {
        $name = self::getName();
        $columns = array_values($name::getColumns()->getColumns());
        //jump pk
        $firstColumnName = $columns[1]->getName();
        return $this->getValue($firstColumnName);
    }

    public function getTitleLabel()
    {
        return '';
    }

    /**
     * Return the string representation of this Model
     * Normally the first meaningfull string property
     * This method call getOptionLabel internally
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getOptionLabel() . '';
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
     * Return a value from model, detect set/get or public variable.
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
     * Define a value in current model.
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
            if ($column instanceof \Db\Column\Column)
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
     * Convert a model to an simple array.
     * It works even if model has private methods
     *
     * @return array
     */
    public function getArray()
    {
        $name = self::getName();
        $temp = (array) ($this);
        $array = array();

        foreach ($temp as $property => $value)
        {
            $property = preg_match('/^\x00(?:.*?)\x00(.+)/', $property, $matches) ? $matches[1] : $property;
            $getValue = $this->getValue($property);
            $value = $getValue ? $getValue : $value;

            if ($value instanceof \Type\Generic)
            {
                $value = $value->toDb();
            }

            if ($value instanceof \Db\Model)
            {
                $value = $value->getArray();
            }

            $array[$property] = $value;
        }

        return $array;
    }

    /**
     * Return a json string representation of this model
     *
     * @return string
     */
    public function toJson()
    {
        return json_encode($this->getArray());
    }

    /**
     * Implements the default PHP json serialize method
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return $this->getArray();
    }

    /**
     * Get model related to this property
     *
     * @param string $propertyName property name
     * @param bool $useCache if use cache to get this object
     * @return \Db\Model
     */
    public function getRelatedModel($propertyName, $useCache = false)
    {
        $name = self::getName();
        $columns = $name::getColumns();
        $column = $columns[$propertyName];
        $column instanceof \Db\Column\Column;

        $idValue = $this->$propertyName;

        $referenceModelClassName = '\Model\\' . $column->getReferenceTable();
        $referenceModel = $referenceModelClassName::findOneByPk($idValue, $useCache);

        return $referenceModel;
    }

    /**
     * Supports the search for variables in the model.
     * Even if they are not declared.
     *
     * In other words variables declared without support models,
     * but avoids error when using PHP_STRICT.
     *
     * @param string $name the variable name
     * @return mixed the value of the variable
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
     * Order and array of models by a passed property.
     * Method suppor array of classes ou array of array.
     * @deprecated since version 14/01/2019 use \Db\Collection
     *
     * @param array $array
     * @param string $property
     * @return array
     */
    public static function orderArrayByProperty($array, $property)
    {
        $collection = new \Db\Collection($array);
        $collection->indexByProperty($property);

        return $collection->getData();
    }

    /**
     * Mount WHERE based on array of filters
     * @deprecated since version 18/02/2020
     *
     * @param array $filters \Db\Cond or \Db\Where
     * @return \stdClass
     * @throws \Exception
     */
    public static function getWhereFromFilters($filters)
    {
        return \Db\Criteria::createCriteria(\Db\Model::parseSearchColumnWhere($filters, self::getName()));
    }

    public static function getRelations()
    {
        $relations = self::defineRelations();

        return $relations;
    }

    public static function defineRelations()
    {
        $name = self::getName();
        $schemaName = \Db\Column\Collection::getSchemaClassName($name);
        $relations = [];

        if (class_exists($schemaName))
        {
            $relations = $schemaName::defineRelations();
        }
        else
        {
            $relations = \Db\Model::mountForeignRelations();
        }

        return $relations;
    }

    public static function mountForeignRelations()
    {
        $name = self::getName();
        $columns = $name::getColumns();
        $relations = [];

        foreach ($columns as $column)
        {
            $column instanceof \Db\Column\Column;
            $referenceModel = $column->getReferenceTable();

            if ($referenceModel)
            {
                $referenceModelClass = '\Model\\' . $referenceModel;
                $referenceField = $column->getReferenceField();
                $referenceTable = $referenceModelClass::getTableName();
                $sql = 'cliente.id = clienteTipo.idCliente';
                $sql = $column->getTableName() . '.' . $column->getName() . ' = ' . $referenceTable . '.' . $referenceField;
                $relations[] = new \Db\Relation($referenceModelClass, $sql);
            }
        }

        return $relations;
    }

}
