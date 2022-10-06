<?php

namespace View\Ext;

/**
 * View baseada em AutoNumeric http://www.decorplanit.com/plugin/
 * Limita a precisão, máxima e mínima de um campo float
 */
class FloatInput extends \View\Input
{

    public function __construct($idName, $value = NULL, $precision = 2, $currency = NULL, $vMax = 99999999999999, $vMin = -99999999999999)
    {
        parent::__construct($idName, \View\Input::TYPE_TEL, $value);

        $this->setPrecision($precision);

        if ($currency)
        {
            $this->setAttribute('data-a-sign', 'R$ ');
        }

        $this->setAttribute('data-a-sep', '.');
        $this->setAttribute('data-a-dec', ',');
        $this->setAttribute('data-v-max', $vMax);
        $this->setAttribute('data-v-min', $vMin);

        $this->setClass('float');
    }

    /**
     * Set precision
     *
     * @param string $precision
     * @return \View\Ext\FloatInput
     */
    public function setPrecision($precision)
    {
        if ($precision == NULL)
        {
            $precision = 2;
        }
        //10,2 mysql
        else if (stripos($precision, ','))
        {
            $explode = explode(',', $precision);
            $precision = $explode[1];
        }

        return $this->setAttribute('data-m-dec', (int) $precision);
    }

    /**
     * Return the precision
     *
     * @return int
     */
    public function getPrecision()
    {
        return (int) $this->getAttribute('data-m-dec');
    }

    public function setValue($value)
    {
        //if is not and type, convert to it, to ensured that the
        //information will be right in the field
        if (!$value instanceof \Type\Generic)
        {
            $value = new \Type\Decimal($value);
        }

        //remove R$ from real
        $value = str_replace('R$ ', '', $value->getValue());

        return parent::setValue($value);
    }

}
