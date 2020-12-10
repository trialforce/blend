<?php

namespace Db\Schema;

/**
 * Represent a database table structure
 */
abstract class Table extends \Db\Column\Collection
{

    protected $indexes;

    public function __construct($columns = null)
    {
        parent::__construct($columns);
        $this->columns = static::defineColumns();
        $this->indexes = static::defineIndexes();
    }

    public function getIndexes()
    {
        if (!$this->indexes)
        {
            $this->indexes = static::defineIndexes();
        }

        return $this->indexes;
    }

    public function setIndexes($indexes)
    {
        $this->indexes = $indexes;
        return $this;
    }

    public abstract static function defineColumns();

    public abstract static function defineIndexes();
}
