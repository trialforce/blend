<?php

namespace View\Svg;

/**
 * Svg Root element
 */
class Svg extends \View\DomContainer
{

    public function first()
    {
        $first = $this->domElement->firstChild;

        if ($first instanceof \DOMText)
        {
            $first = $first->nextSibling;
        }

        if ($first instanceof \DOMText)
        {
            $first = $first->nextSibling;
        }

        return new \View\DomContainer($first);
    }

    /**
     * Set width
     * @param string $width the width
     * @return \View\Svg\Svg
     */
    public function setWidth($width)
    {
        return $this->attr('width', $width);
    }

    public function getWidth()
    {
        return $this->getAttribute('width');
    }

    /**
     * Set weight
     * @param string $height the height
     * @return \View\Svg\Svg
     */
    public function setHeight($height)
    {
        return $this->attr('height', $height);
    }

    public function getHeight()
    {
        return $this->getAttribute('height');
    }

    public function setViewBox($viewBox)
    {
        return $this->attr('viewBox', $viewBox);
    }

    public function getViewBox()
    {
        return $this->getAttribute('viewBox');
    }

}
