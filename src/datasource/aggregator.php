<?php

namespace DataSource;

class Aggregator
{

    const METHOD_SUM = 'sum';
    const METHOD_COUNT = 'count';
    const METHOD_AVG = 'avg';
    const METHOD_MIN = 'min';
    const METHOD_MAX = 'max';

    /**
     * Aggregator method
     *
     * @var string
     */
    private $method;

    /**
     * Aggregator column name
     *
     * @var string
     */
    private $columnName;

    /**
     * Aggregator label
     *
     * @var string
     */
    private $label;

    /**
     *
     * @param string $columnName
     * @param string $method
     */
    public function __construct( $columnName, $method = self::METHOD_SUM )
    {
        $this->columnName = $columnName;
        $this->method = $method;
    }

    public function getMethod()
    {
        return $this->method;
    }

    public function setMethod( $method )
    {
        $this->method = $method;
        return $this;
    }

    public function getColumnName()
    {
        return $this->columnName;
    }

    public function setColumnName( $columnName )
    {
        $this->columnName = $columnName;
        return $this;
    }

    public function getLabel()
    {
        return $this->label;
    }

    public function setLabel( $label )
    {
        $this->label = $label;
        return $this;
    }

    public function getLabelledValue( $value )
    {
        $label = $this->getLabel();

        if ( !$label )
        {
            if ( $this->method == self::METHOD_SUM )
            {
                $label = 'Total:';
            }
            else if ( $this->method == self::METHOD_AVG )
            {
                $label = 'Média:';
            }
            else if ( $this->method == self::METHOD_MAX )
            {
                $label = 'Max.:';
            }
            else if ( $this->method == self::METHOD_MIN )
            {
                $label = 'Min.:';
            }
            else if ( $this->method == self::METHOD_COUNT )
            {
                $label = 'Quant.:';
            }
        }

        return $label . ' ' . $value;
    }

}
