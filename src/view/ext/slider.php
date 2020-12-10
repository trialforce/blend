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

/*<div id="slider2" class="slider" style="width: 300px;">
        <div class="slider-wrapper">
            <div class="slider-items">
                <span class="slide" style="background: #7ADCEF;">Other 1</span>
                <span class="slide" style="background: #FFCF47;">Other 2</span>
                <span class="slide" style="background: #F97C68;">Other 3</span>
                <span class="slide" style="background: #a78df5;">Other 4</span>
                <span class="slide" style="background: #ff8686;">Other 5</span>
            </div>
        </div>
        <div class="slider-control slider-prev"></div>
        <div class="slider-control slider-next"></div>
    </div>*/
