<?php

namespace View;

/**
 * Table column
 */
class Col extends \View\View
{

    public function __construct( $id = \NULL, $innerHtml = \NULL, $span = \NULL, $class = \NULL, $father = \NULL )
    {
        parent::__construct( 'col', $id, $innerHtml, $class, $father );
        $this->setSpan( $span );
    }

    /**
     * Define the span attribute
     *
     * @param int $span
     */
    public function setSpan( $span )
    {
        $this->setAttribute( 'span', $span );
    }

    /**
     * Return the span attribute
     * 
     * @return int
     */
    public function getSpan()
    {
        return $this->getAttribute( 'span' );
    }

}
