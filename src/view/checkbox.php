<?php

namespace View;

/**
 * Html checkbox element
 */
class Checkbox extends \View\Input
{

    public function __construct($idName = NULL, $value = 1, $checked = FALSE, $class = NULL)
    {
        parent::__construct($idName, \View\Input::TYPE_CHECKBOX, $value, $class);
        $this->setChecked($checked);
        $this->setAutoComplete(false);
    }

    public function setValue($value)
    {
        $this->setAttribute('value', $value);
        return $this;
    }

    /**
     * Set checked
     *
     * @param boolean $checked
     * @return \View\Checkbox
     */
    public function setChecked($checked)
    {
        if ($checked . '' >= 1)
        {
            $this->setAttribute('checked', 'checked');
        }
        else
        {
            $this->removeAttribute('checked');
        }

        return $this;
    }

    /**
     * Special set read only
     * @param bool $readOnly
     * @param bool $setInChilds
     */
    public function setReadOnly($readOnly, $setInChilds = FALSE)
    {
        $result = parent::setReadOnly($readOnly, $setInChilds);

        //from http://stackoverflow.com/questions/155291/can-html-checkboxes-be-set-to-readonly
        $this->attr('onclick', 'return false');
        $this->attr('onkeydown', 'return false');

        return $result;
    }

}