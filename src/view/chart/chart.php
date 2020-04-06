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

    /**
     * Create a char from a collection.
     *
     * The collection must have a lista of sdtClass objects with above parameters:
     * id: the id of html element
     * label: the label (html title)
     * color: the html color, optional you can use \Media\Color::rand()
     * percent: the percent
     * offset: and the offset percent
     *
     * @param string $id the id of html element
     * @param \Db\Collection $data the collection
     * @param string $extraClass extra css class
     */
    public static function createFromCollection($id = null, \Db\Collection $data, $extraClass = null);
}
