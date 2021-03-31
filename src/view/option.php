<?php

namespace View;

/**
 * Html option element.
 * Used inside Select
 */
class Option extends \View\View
{

    public function __construct($value = null, $label = NULL, $selected = FALSE, $father = NULL)
    {
        parent::__construct('option', NULL, NULL, NULL, $father);
        $this->setValue($value);
        $this->append($label ? $label : $value);

        if ($selected)
        {
            $this->setAttribute('selected', 'selected');
        }
    }

    /**
     * Create an simple option
     * @param mixed $item anything that is construct an option
     * @param string $index used when is simple array
     * @param \View\View $parent parent element, to add direct when create
     * @return \View\Option the created option
     */
    public static function createOption($item, $index = null, $parent = null)
    {
        $option = null;

        //allready an option
        if ($item instanceof \View\Option)
        {
            $option = $item;

            if ($parent)
            {
                $parent->append($item);
            }
        }
        else if (is_object($item))
        {
            //default method for model
            if (method_exists($item, 'getOptionValue'))
            {
                $option = new \View\Option($item->getOptionValue() . '', $item->getOptionLabel() . '', FALSE, $parent);
            }
            else //simple object
            {
                $values = array_values((array) $item);
                $option = new \View\Option($values[0], isset($values[1]) ? $values[1] : $values[0] . '', FALSE, $parent);
            }
        }
        else //simple array
        {
            $option = new \View\Option($index . '', $item . '', FALSE, $parent);
        }

        return $option;
    }

}
