<?php

namespace View\Ext;

/**
 * Create a Swype box image link
 */
class SwypeBoxLink extends \View\A
{

    /**
     * The image
     *
     * @var \View\Img
     */
    private $img;

    public function __construct($id = \NULL, $label = \NULL, $href = '#', $thumb, $class = \NULL, $target = '_BLANK', $father = NULL)
    {
        //avoids errors
        $target = $target ? $target : '_BLANK';
        $thumb = $thumb ? $thumb : $href;

        $this->img = new \View\Img(NULL, $thumb, NULL, NULL, $label);

        parent::__construct($id, $this->img, $href, 'swipebox ' . $class, $target, $father);

        $this->setTitle($label);
    }

    function getImg()
    {
        return $this->img;
    }

    function setImg(\View\Img $img)
    {
        $this->img = $img;
    }

}