<?php

namespace Db;

/**
 * Search Column (subselect)
 */
class SearchColumn extends \Db\Column
{

    /**
     * Subselect to be executed
     *
     * @var string
     */
    protected $query;

    /**
     * Construct the search column
     *
     * @param string $label
     * @param string $name
     * @param string $type
     * @param string $query
     */
    public function __construct($label = NULL, $name = NULL, $type = NULL, $query = NULL)
    {
        parent::__construct($label, $name, $type);
        $this->setQuery($query);
    }

    /**
     * Return the query (subselect)
     *
     * @return string
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * Define the query (subselect)
     *
     * @param string $query
     */
    public function setQuery($query)
    {
        //convert a \Db\Query to a simple string to searchColumn
        if ($query instanceof \Db\QueryBuilder)
        {
            $query = $query->getSelectSql()->replace('SELECT', '')->__toString();
        }

        $this->query = $query;
    }

    /**
     * Return the query to use in sql
     *
     * @return string
     */
    public function getSql($withAs = TRUE)
    {
        $columnName = $this->getName();
        $columnQuery = $this->getQuery();
        $sql = "( SELECT $columnQuery )";

        if ($withAs)
        {
            $sql .= "AS $columnName";
        }

        $result[] = $sql;

        return $result;
    }

}
