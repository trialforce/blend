<?php

namespace View;

/**
 * NOScript html element
 */
class NoScript extends \View\View
{
    public function __construct($src, $content = \NULL)
    {
        parent::__construct('noscript');
        $this->setAttribute('src', $src);
        $this->append($content);
    }


}
