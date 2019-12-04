<?php

namespace Db\Column;

/**
 * Group Column (subselect)
 */
class Group extends \Db\Column\Column
{

    const METHOD_GROUP = 'group';
    const METHOD_SUM = 'sum';
    const METHOD_COUNT = 'count';
    const METHOD_AVG = 'avg';
    const METHOD_MIN = 'min';
    const METHOD_MAX = 'max';

    protected $query;

    /**
     *
     * @var string
     */
    protected $agg;

    public function __construct($label = NULL, $agg = NULL, $query = NULL, $name = NULL, $type = null)
    {
        parent::__construct($label, $name, $type);
        $this->setAgg($agg);
        $this->setQuery($query);
    }

    /**
     * Group query
     *
     * @return string
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * Define the group query
     *
     * @param string $query
     */
    public function setQuery($query)
    {
        $this->query = $query;
    }

    /**
     * Return the aggregation function
     *
     * @return string
     */
    public function getAgg()
    {
        return $this->agg;
    }

    /**
     * Define the aggregation function
     *
     * @param string $agg
     */
    public function setAgg($agg)
    {
        $this->agg = $agg;
    }

    /**
     * Return the query to sql
     *
     * @return string
     */
    public function getSql()
    {
        if (!$this->getAgg() || $this->getAgg() == self::METHOD_GROUP)
        {
            $result[] = $this->getQuery() . ' AS ' . $this->getProperty();
        }
        else
        {
            $result[] = $this->getAgg() . '(' . $this->getQuery() . ') AS ' . $this->getProperty();
        }

        return $result;
    }

    /**
     * Only to add compatibilty with normal column and \Component\Grid\MountFilter
     *
     * @return boolean
     */
    public function getFilter()
    {
        return false;
    }

    /**
     * List all group types
     *
     * @return array
     */
    public static function listGroupTypes()
    {
        $result = array();
        $result[self::METHOD_GROUP] = 'Agrupar';
        $result[self::METHOD_COUNT] = 'Contagem';
        $result[self::METHOD_SUM] = 'Somar';
        $result[self::METHOD_AVG] = 'Média';
        $result[self::METHOD_MAX] = 'Máximo';
        $result[self::METHOD_MIN] = 'Mínimo';

        return $result;
    }

    /**
     * Return method name
     *
     * @param string $method
     * @return string
     */
    public static function getMethodLabel($method)
    {
        $list = self::listGroupTypes();

        if (isset($list[$method]))
        {
            return $list[$method];
        }

        return $method;
    }

}
