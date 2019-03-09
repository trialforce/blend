<?php

namespace DataSource;

/**
 * Query datasource
 */
class Query extends DataSource
{

    /**
     * Tabelas da query
     *
     * @var string
     */
    protected $table;

    /**
     * Campos da query
     * @var array
     */
    protected $fields;

    /**
     * Os dados
     * @var array
     */
    protected $data = NULL;

    /**
     * Define a classe do modelo na qual a query será retornada
     * @var string
     */
    protected $modelClass;

    /**
     * Constroi o negócio
     *
     * @param string $tables
     * @param array $fields
     */
    public function __construct($tables, array $fields)
    {
        $this->setTable($tables);
        $this->setFields($fields);
    }

    /**
     * Retorna a tabela
     * @return string
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * Define a tabela
     *
     * @param string $table
     * @return \DataSource\Query
     */
    public function setTable($table)
    {
        $this->table = $table;
        return $this;
    }

    /**
     * Retorna os campos
     * @return array
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * Retorna os campos como string
     *
     * @return string
     */
    public function getFieldsString()
    {
        $fields = $this->getFields();

        return implode(' , ', $fields);
    }

    /**
     * Retorna os campos
     *
     * @param string $fields
     * @return \DataSource\Query
     */
    public function setFields($fields)
    {
        $this->fields = $fields;
        return $this;
    }

    /**
     * Retorna a classe do modelo usado pro retorno da query
     * @return string
     */
    public function getModelClass()
    {
        return $this->modelClass;
    }

    /**
     * Define a classe do modelo usado pro retorno da query
     * @param string $classModel
     * @return \DataSource\Query
     */
    public function setModelClass($modelClass)
    {
        $this->modelClass = $modelClass;
        return $this;
    }

    /**
     * Faz a contagem dos registro
     *
     * @return string
     */
    public function getCount()
    {
        if (is_null($this->count))
        {
            $where = $this->getWhere();
            $sql = \Db\Catalog\Mysql::mountSelect($this->getTable(), 'count(*) AS count', $where->sql);
            $result = \Db\Conn::getInstance()->query($sql, $where->args, $this->getModelClass());
            $this->count = $result[0]->count;
        }

        return $this->count;
    }

    /**
     * Executa a query no banco retornando os dados
     *
     * @return array
     */
    public function getData()
    {
        if (is_null($this->data))
        {
            $where = $this->getWhere();
            $sql = \Db\Catalog\Mysql::mountSelect($this->getTable(), $this->getFieldsString(), $where->sql, $this->getLimit(), $this->getOffset(), NULL, $where->having, $this->getOrderBy(), $this->getOrderWay());
            $this->data = \Db\Conn::getInstance()->query($sql, $where->args, $this->getModelClass());
        }

        return $this->data;
    }

    /**
     * Monta as condições de busca
     *
     * @return string
     */
    protected function getWhere()
    {
        $filterColumns = array();
        $columns = $this->getColumns();

        foreach ($columns as $column)
        {
            if ($column->getFilter())
            {
                $filterColumns[] = $column;
            }
        }

        return \Db\Model::getWhereFromFilters(\Db\Model::smartFilters($this->getSmartFilter(), $this->getExtraFilter(), $filterColumns));
    }

    /**
     * Monta as colunas, nada por enquanto
     *
     * TODO explodir $this->fields e fazer a magia
     *
     * @return null
     */
    public function mountColumns()
    {
        //Query não rola com colunas automáticas
        return NULL;
    }

    public function executeAggregator(Aggregator $aggregator)
    {

    }

}
