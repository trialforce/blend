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
     * @param string $icon
     * @param sring $label
     * @param string $action
     * @param string $class
     * @param string $title
     * @param bool $formChangeAdvice
     */
    public function addItem($id,$icon, $label, $action, $class = NULL, $title = NULL, $formChangeAdvice = FALSE)
    {
        $field = new \View\Span(null, array(new \View\Ext\Icon($icon),$label));

        $item = new \View\Li($id, $field, $class);
        $item->click($action)->setTitle($title);
        
        if ( $formChangeAdvice)
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
    public function addItemLink($id,$icon, $label, $action, $class = NULL, $title = NULL, $formChangeAdvice = FALSE)
    {
        $field = new \View\A(null, array(new \View\Ext\Icon($icon),$label),$action);
        $field->setTarget('_BLANK');

        $item = new \View\Li($id, $field, $class);
        $item->setTitle($title);
        
        if ( $formChangeAdvice)
        {
            $item->formChangedAdvice(true);
        }

        $this->append($item);
    }

}
