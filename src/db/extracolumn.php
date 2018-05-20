<?php

namespace Db;

/**
 * Informations about one database column
 */
class ExtraColumn extends Column
{

    /**
     * Extra column id
     *
     * @var int
     */
    protected $id;

    /**
     * Css classe
     *
     * @var string
     */
    protected $cssClass;

    /**
     * Show in grid
     *
     * @var boolean
     */
    protected $showGrid;

    /**
     * Return id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Define id
     *
     * @param int $id
     * @return \Db\ExtraColumn
     */
    public function setId( $id )
    {
        $this->id = $id;
        return $this;
    }

    public function getCssClass()
    {
        return $this->cssClass;
    }

    public function getShowGrid()
    {
        return $this->showGrid;
    }

    public function setCssClass( $cssClass )
    {
        $this->cssClass = $cssClass;
        return $this;
    }

    public function setShowGrid( $showGrid )
    {
        $this->showGrid = $showGrid;
        return $this;
    }

    /**
     * Return the query to use in sql
     *
     * @return string
     */
    public function getSql()
    {
        return NULL;
    }

}
