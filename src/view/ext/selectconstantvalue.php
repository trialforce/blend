<?php

namespace View\Ext;

class SelectConstantValue extends \View\Select
{

    protected $radios;
    protected $value;

    public function __construct(\Db\Column $column, $columName)
    {
        $constantValues = $column->getConstantValues();
        $options = array();

        if ($constantValues instanceof \Db\ConstantValues)
        {
            $constantValues = $constantValues->getArray();
        }

        if (is_array($constantValues))
        {
            foreach ($constantValues as $value => $label)
            {
                $options[] = new \View\Option($value, $label);
            }
        }

        parent::__construct($columName);
        $this->append($options);
    }

}
