<?php

namespace View\Blend;

class FloatingMenu extends \View\Ul
{

    /**
     * Create a simple Floating Menu
     *
     * @param string $id
     * @param mixed $innerHtml
     * @param string $class
     */
    public function __construct($id = NULL, $innerHtml = NULL, $class = NULL)
    {
        parent::__construct($id, $innerHtml, $class);
        $this->addClass('blend-floating-menu');
    }

    /**
     * Add and item to menu
     *
     * @param string $icon icon
     * @param sring $label label
     * @param string $action action
     * @param string $class class
     * @param string $title tittle
     * @param bool $formChangeAdvice form change advice
     */
    public function addItem($id, $icon, $label, $action, $class = NULL, $title = NULL, $formChangeAdvice = FALSE)
    {
        $icon = is_string($icon) ? new \View\Ext\Icon($icon) : $icon;
        $field = new \View\Span(null, array($icon, $label));

        $item = new \View\Li($id, $field, $class);
        $item->setTitle($title);

        if ($action)
        {
            $item->click($action);
        }

        if ($formChangeAdvice)
        {
            $item->formChangedAdvice(true);
        }

        $this->append($item);
    }

    /**
     * Add and item to menu
     *
     * @param string $icon
     * @param sring $label
     * @param string $action
     * @param string $class
     * @param string $title
     * @param bool $formChangeAdvice
     */
    public function addItemLink($id, $icon, $label, $action, $class = NULL, $title = NULL, $formChangeAdvice = FALSE)
    {
        $field = new \View\A(null, array(new \View\Ext\Icon($icon), $label), $action);
        $field->setTarget('_BLANK');

        $item = new \View\Li($id, $field, $class);
        $item->setTitle($title);

        if ($formChangeAdvice)
        {
            $item->formChangedAdvice(true);
        }

        $this->append($item);
    }

}
