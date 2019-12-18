<?php

namespace View;

/**
 * Svg Document
 */
class Svg extends \View\Document
{

    public function __construct($path)
    {
        parent::__construct();
        $this->loadXmlFromFile($path);
    }

    /**
     * Return the SVG root
     *
     * @return \View\DomContainer
     */
    public function getSvgRoot()
    {
        return new \View\DomContainer($this->firstChild);
    }

}
