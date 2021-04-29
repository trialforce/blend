<?php

namespace View;

/**
 * Html OptGroup element.
 * Used inside Select
 */
class OptGroup extends \View\View
{

    public function __construct($id = NULL, $label = NULL, $options = NULL, $class = NULL, $father = NULL)
    {
        parent::__construct('optgroup', $id, $options, $class, $father);
        $this->attr('label', $label);
        $this->append($label);
    }

}
