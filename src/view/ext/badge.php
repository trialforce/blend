<?php

namespace View\Ext;

/**
 * Simple badge
 *
 * Remenber to add badge.css to your theme
 */
class Badge extends \View\Span
{

    /**
     *
     * @param string $id the attr id from html
     * @param mixed $innerHtml content of the badge
     * @param string $class extras css class, can be color or size
     */
    public function __construct($id = \NULL, $innerHtml = \NULL, $class = \NULL)
    {
        parent::__construct($id, $innerHtml, 'badge ' . $class);
    }

}
