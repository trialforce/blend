<?php

namespace DataSource\Aggregator;

class Bytes extends \DataSource\Aggregator
{

    public function __construct($columnName, $method = \DataSource\Aggregator::METHOD_SUM)
    {
        parent::__construct($columnName, $method);
    }

    public function getLabelledValue($value)
    {
        $value = new \Type\Bytes($value);

        return parent::getLabelledValue($value);
    }

}
