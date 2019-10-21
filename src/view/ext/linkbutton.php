<?php

namespace View\Ext;

/**
 * A link that visuale behaves like a button
 *
 * The class as a smart control the support p (post) urls,
 * and it supports external link with target _BLANK. (automaticlly)
 */
class LinkButton extends \View\A
{

    public function __construct($id, $icon, $label, $href = '#', $class = NULL, $target = NULL, $father = NULL)
    {
        $onclick = null;
        $url = $href;

        //little workaround to remove p( in url
        if (stripos($href, 'p(') === 0)
        {
            $url = str_replace(array("p('", 'p("', "')", '")', ';'), '', $url);
            $onclick = $href;
        }

        parent::__construct($id, NULL, $url, $class, $target, $father);

        if ($onclick)
        {
            $this->click($onclick);
        }

        //if is a complete link, open it in other url
        if ($target == NULL && stripos($url, 'http') === 0)
        {
            $this->setTarget('_BLANK');
        }

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
