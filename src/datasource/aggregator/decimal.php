<?php

namespace DataSource\Aggregator;

class Decimal extends \DataSource\Aggregator
{

    public function __construct($columnName, $method = \DataSource\Aggregator::METHOD_SUM)
    {
        parent::__construct($columnName, $method);
    }

    public function getLabelledValue($value)
    {
        $value = new \Type\Decimal($value);

        return parent::getLabelledValue($value);
    }

}
