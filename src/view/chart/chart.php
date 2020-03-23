<?php

namespace View\Chart;

/**
 * Simple chat interface
 */
interface Chart
{

    /**
     * Adds a segment to graph
     *
     * @param string $id html element id
     * @param float $percent percent
     * @param float $offset
     *  @return \View\View the resultant segment
     */
    public function addSegment($id = null, $color = null, $percent, $offset = 0);

    /**
     * Add a label to graph
     *
     * @param string $id the html element id
     * @param string $text
     * @return \View\View the resultant label
     */
    public function addLabel($id, $text);
}
