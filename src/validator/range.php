<?php

namespace Validator;

/**
 * Range int validator
 *
 */
class Range extends \Validator\Validator
{

    /**
     * Mini
     * @var int
     */
    protected $min;

    /**
     * Max
     *
     * @var int
     */
    protected $max;

    public function getMin()
    {
        return $this->min;
    }

    public function getMax()
    {
        return $this->max;
    }

    public function setMin( $min )
    {
        $this->min = $min;
        return $this;
    }

    public function setMax( $max )
    {
        $this->max = $max;
        return $this;
    }

    public function validate( $value = NULL )
    {
        $error = parent::validate( $value );

        if ( $this->value || $this->value === '0' )
        {
            $this->value = floatval( $value );

            if ( $this->min && !$this->max && ( $this->value < $this->min) )
            {
                $error[] = 'Valor deve ser maior que ' . $this->min . '.';
            }
            else if ( $this->max && !$this->min && ( $this->value > $this->max) )
            {
                $error[] = 'Valor deve ser menor que ' . $this->max . '.';
            }
            else if ( ( $this->value > $this->max) || ( $this->value < $this->min) )
            {
                $error[] = 'Valor deve estar entre ' . $this->min . ' e ' . $this->max . '.';
            }
        }

        return $error;
    }

}
