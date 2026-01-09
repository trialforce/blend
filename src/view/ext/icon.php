<?php

namespace View\Ext;

/**
 * Font awesome icon
 */
class Icon extends \View\I
{
    
    /**
     * Construct a font-awesome icon
     * @param string $icon the icon css class
     * @param string $id the id (optional)
     * @param string $onClick the on click (optional)
     * @param string $extraClass extra css class
     * @param string $title html title element
     */
    public function __construct($icon, $id = NULL, $onClick = NULL, $extraClass = NULL, $title = NULL)
    {
        //FIXME still needed?
        $icon = str_replace('cancel', 'times', $icon);
        
        $iconAwesome = 'fontawesome';
        $iconLib = \DataHandle\Config::getDefault('iconLib', $iconAwesome);

        // as default, the icons are identified by 'fa fa-icone'
        // if another package is needed, like 'fab', cannot change the name here
        if (!(stripos($icon, 'fa') === 0) && $iconLib == $iconAwesome)
        {
            $icon = 'fa fa-' . $icon;
        }

        parent::__construct($id, null, $iconLib == $iconAwesome? $icon : null);
        $this->addClass($extraClass);
        $this->setTitle($title);
        
        $this->createLucide($icon);

        if ($onClick)
        {
            $this->click($onClick);
        }
    }
    
    private function createLucide($icon)
    {
        $iconLib = \DataHandle\Config::getDefault('iconLib', 'fontawesome');
        
        if ($iconLib != 'lucide')
        {
            return false;
        }
        
        $icon = str_replace('layer-group', 'layers', $icon);
        $icon = str_replace('undo', 'undo-2', $icon);
        $icon = str_replace('fa fa-square-o', 'square', $icon);
        $icon = str_replace('fa fa-check-square-o', 'square-check-big', $icon);
        $icon = str_replace('times remove', 'x', $icon);
        $icon = str_replace('times', 'x', $icon);
        $icon = str_replace('cancel', 'x', $icon);
        $this->setData('lucide', $icon);
    }

}
