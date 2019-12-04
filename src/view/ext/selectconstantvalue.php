<?php

namespace View\Ext;

/**
 * A simple \View\Select mounted by a list of constant values
 */
class SelectConstantValue extends \View\Select
{

    public function __construct(\Db\Column\Column $column, $columName)
    {
        $constantValues = $column->getConstantValues();
        $options = array();

        if ($constantValues instanceof \Db\ConstantValues)
        {
            $constantValues = $constantValues->getArray();
        }

        if (isIterable($constantValues))
        {
            foreach ($constantValues as $value => $label)
            {
                $options[] = \View\Option::createOption($label, $value);
            }
        }

        parent::__construct($columName);
        $this->append($options);
    }

}
