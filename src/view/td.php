<?php

namespace View;

/**
 * Table cell html element
 */
class Td extends \View\View
{

    public function __construct($id = \NULL, $innerHtml = \NULL, $class = \NULL)
    {
        parent::__construct('td', $id, $innerHtml, $class);
    }

    /**
     * Define colspan
     *
     * @param int $colspan colspan
     * @return \View\Td
     */
    public function setColSpan($colspan)
    {
        return $this->setAttribute('colspan', $colspan);
    }

    /**
     * Return the colspan
     *
     * @return int
     */
    public function getColSpan()
    {
        return $this->getAttribute('colspan');
    }

    /**
     * Define row span
     *
     * @param int $rowspan colspan
     * @return \View\Td
     */
    public function setRowSpan($rowspan)
    {
        return $this->setAttribute('rowspan', $rowspan);
    }

    /**
     * Retutn the row span
     *
     * @return int
     */
    public function getRowSpan()
    {
        return $this->getAttribute('rowspan');
    }

}