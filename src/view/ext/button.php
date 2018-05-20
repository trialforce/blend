<?php

namespace View\Ext;

/**
 * Button with icon
 */
class Button extends \View\Button
{

    const BTN_PRIMARY = 'primary';
    const BTN_INFO = 'info';
    const BTN_SUCCESS = 'success';
    const BTN_WARNING = 'warning';
    const BTN_DANGER = 'danger';
    const BTN_INVERSE = 'inverse';
    const BTN_LINK = 'link';
    const BTN_FEATURE = 'feature';
    const BTN_WHITE = 'white';
    const BTN_SMALL = 'small';

    protected $icon;

    /**
     * Construct the button
     *
     * @param string $idName
     * @param string $icon
     * @param string $label
     * @param string $onClick
     * @param string $class
     */
    public function __construct($idName, $icon, $label, $onClick = NULL, $class = NULL, $title = NULL)
    {
        parent::__construct($idName, NULL, $onClick, 'btn');
        $this->setAttribute('type', 'button');
        $this->addClass($class);
        $this->setIcon($label, $icon);
        $this->setTitle($title);
    }

    public function setIcon($label, $icon)
    {
        $this->clearChildren();

        if ($icon)
        {
            $newLabel[] = $this->icon = new \View\Ext\Icon($icon);
        }

        $newLabel[] = new \View\Span(null, ' ' . $label, 'btn-label');

        $this->append($newLabel);
    }

    public function getIcon()
    {
        return $this->icon;
    }

}