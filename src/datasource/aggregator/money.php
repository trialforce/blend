<?php

namespace DataSource\Aggregator;

class Money extends \DataSource\Aggregator
{

    public function __construct( $columnName, $method = \DataSource\Aggregator::METHOD_SUM )
    {
        parent::__construct( $columnName, $method );
    }

    public function getLabelledValue( $value )
    {
        $value = new \Type\Money( $value );

        return parent::getLabelledValue( $value );
    }

}
