<?php

namespace View\Svg;

/**
 * Svg Document
 */
class Document extends \View\Document
{

    public function __construct($path = null, $setDom = false)
    {
        parent::__construct(null, $setDom);

        if ($path)
        {
            $this->loadXmlFromFile($path);
        }
        else
        {
            //if don't has a svg to load, create a simple SVG element
            $this->loadXML('<svg version="1.1"></svg>');
        }
    }

    /**
     * Return the SVG root
     *
     * @return \View\Svg\Svg
     */
    public function getSvgRoot()
    {
        return new \View\Svg\Svg($this->firstChild);
    }

    public function append(...$nodes):void
    {
        foreach ($nodes as $content)
        {
            if ($content instanceof \View\Svg\Document)
            {
                $svgRoot = $content->byTag('g');

                if ($svgRoot)
                {
                    $cloned = $this->importNode($svgRoot->getDomElement(), true);
                    $this->getSvgRoot()->append($cloned);
                }
            }

            parent::append($content);
        }
    }

}
