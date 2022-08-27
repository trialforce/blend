<?php

namespace View\Ext;

/**
 * Blend default slider
 */
class Slider extends \View\Div
{

    protected $items;

    public function __construct($id = \NULL, $extraClass = '')
    {

        $this->items = new \View\Div(null, null, 'slider-items');
        $wrapper = new \View\Div(null, null, 'slider-wrapper');
        $wrapper->append($this->items);

        parent::__construct($id, $wrapper, 'slider');
        $this->addClass($extraClass);
    }

    public function addNextPrevControl()
    {
        $controls[] = new \View\Div(null, null, 'slider-control slider-prev');
        $controls[] = new \View\Div(null, null, 'slider-control slider-next');
        $this->append($controls);

        return $this;
    }

    public function addSlide(\View\View $view)
    {
        $view->addClass('slide');
        $this->items->append($view);
    }

}
