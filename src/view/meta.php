<?php

namespace View;

/**
 * Html meta tag
 */
class Meta extends \View\View
{

    public function __construct($name = NULL, $innerHtml = NULL, $property = null, $content = null)
    {
        parent::__construct('meta');
        $this->setAttribute('name', $name);
        $this->append($innerHtml);

        $this->setProperty($property);
        $this->setContent($content);
    }

    /**
     * Set content
     *
     * @param string $content content
     * @return $this
     *
     */
    public function setContent($content)
    {
        return $this->setAttribute('content', $content);
    }

    /**
     * Set property
     *
     * @param string $property property
     * @return #this
     */
    public function setProperty($property)
    {
        return $this->setAttribute('property', $property);
    }

}
