<?php

namespace View\Ext;

class LinkButton extends \View\A
{

    public function __construct($id, $icon, $label, $href = '#', $class = NULL, $target = NULL, $father = NULL)
    {
        parent::__construct($id, NULL, $href, $class, $target, $father);

        $this->addClass('btn');

        $this->setIcon($label, $icon);
    }

    public function setIcon($label, $icon, $color = 'white')
    {
        $this->clearChildren();

        if ($icon)
        {
            $newLabel[] = $this->icon = new \View\Ext\Icon($icon, $color);
        }

        $newLabel[] = new \View\Span(null, ' ' . $label, 'btn-label');

        $this->append($newLabel);
    }

}