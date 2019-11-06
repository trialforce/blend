<?php

namespace Db\Column;

/**
 * Search Column (subselect)
 */
class Search extends \Db\Column\Column
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
     * @param string $label the label of the column
     * @param string $name the name of the colum (the AS of the select
     * @param string $type the column type
     * @param string $query the column subselect
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

        return $this;
    }

    public function getReferenceSql($withAs = TRUE, $referencedColumn = NULL)
    {
        if (!$referencedColumn)
        {
            $columnQuery = $this->getQuery();
            $referencedColumn = "( SELECT $columnQuery )";
        }

        return parent::getReferenceSql($withAs, $referencedColumn);
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

        if ($this->getReferenceDescription())
        {
            $result[] = $this->getReferenceSql(TRUE);
        }

        return $result;
    }

}
