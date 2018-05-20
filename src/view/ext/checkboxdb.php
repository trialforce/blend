<?php

namespace View\Ext;

/**
 * Html checkbox element
 */
class CheckboxDb extends \View\Checkbox
{

    public function setValue($value)
    {
        $this->setAttribute('value', 1);
        return $this->setChecked($value);
    }

}
