<?php

namespace Db\Column;

/**
 * Search Column in a father
 * Uses subselect
 */
class FatherSearch extends \Db\Column\Search
{

    /**
     * Subselect to be executed
     *
     * @var string
     */
    protected $query;
    protected $searchQuery;
    protected $columnValue;
    protected $columnDescription;

    /**
     * Construct the search column
     *
     * @param string $label the label of the column
     * @param string $name the name of the colum (the AS of the select)
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
        $this->query = $query;

        return $this;
    }

    public function getSearchQuery()
    {
        return $this->searchQuery;
    }

    public function setSearchQuery($searchQuery)
    {
        $this->searchQuery = $searchQuery;
        return $this;
    }

    public function getColumnValue()
    {
        return $this->columnValue;
    }

    public function getColumnDescription()
    {
        return $this->columnDescription;
    }

    public function setColumnValue($columnValue)
    {
        $this->columnValue = $columnValue;
        return $this;
    }

    public function setColumnDescription($columnDescription)
    {
        $this->columnDescription = $columnDescription;
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

    public function getFilterClassName(): string
    {
        return '\Filter\FatherSearch';
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
        $columnValue = $this->getColumnValue();
        $columnDescription = $this->getColumnDescription();
        $sql = "( SELECT $columnValue FROM $columnQuery )";

        if ($withAs)
        {
            $sql .= " AS $columnName";
        }

        $result[] = $sql;

        if ($withAs)
        {
            $sql = "( SELECT $columnDescription FROM $columnQuery ) AS {$columnName}Description";

            $result[] = $sql;
        }

        return $result;
    }

}
