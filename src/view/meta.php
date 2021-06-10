<?php

namespace View;

/**
 * Html meta tag
 */
class Meta extends \View\View
{

    public function __construct($name = NULL, $innerHtml = NULL, $content = null)
    {
        parent::__construct('meta');
        $this->setAttribute('name', $name);
        $this->append($innerHtml);
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

}
