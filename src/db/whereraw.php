<?php

namespace Db;

class WhereRaw extends \Db\Where
{

    public function getString($first = false)
    {
        return $this->getFilter();
    }

    public function getWhereSql($first = true)
    {
        return $this->getFilter();
    }

    public function getStringPdo($first = false)
    {
        return $this->getFilter();
    }

    public function __toString()
    {
        return $this->getFilter();
    }

}
