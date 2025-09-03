<?php

namespace View;

/**
 * Html table element
 */
class Table extends \View\View
{

    /**
     * Construct the html table element
     *
     * @param null $id the id of element, can be null
     * @param mixed $innerHtml content of element, can be null
     * @param null $class html class attribute, used in css, can be null
     * @param null $caption table caption
     * @param null $summary
     * @throws \Exception
     */
    public function __construct( $id = \NULL, $innerHtml = \NULL, $class = \NULL, $caption = NULL, $summary = NULL )
    {
        parent::__construct( 'table', $id, null, $class );

        if ( $caption )
        {
            $this->addCaption( $caption );
        }

        if ( $summary )
        {
            $this->setSummary( $summary );
        }

        $this->append($innerHtml);
    }

    /**
     * Add a caption to this table
     *
     * @param string $caption table caption
     *
     * @return \View\Table
     * @throws \Exception
     */
    public function addCaption( $caption )
    {
        $this->append( new Caption( NULL, $caption ) );

        return $this;
    }

    /**
     * Define summary
     *
     * @param string $summary the summary
     * @return \View\Table
     */
    public function setSummary( $summary )
    {
        return $this->setAttribute( 'summary', $summary );
    }

    /**
     * Get summary
     *
     * @return string
     */
    public function getSummary()
    {
        return $this->getAttribute( 'summary' );
    }

}
