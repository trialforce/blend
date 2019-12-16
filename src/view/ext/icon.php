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
     */
    public function __construct($icon, $id = NULL, $onClick = NULL, $extraClass = NULL)
    {
        //FIXME still needed?
        $icon = str_replace('cancel', 'times', $icon);
        
        // as default, the icons are identified by 'fa fa-icone'
        // if another package is needed, like 'fab', cannot change the name here
        if (!(stripos($icon, 'fa') === 0))
        {
            $icon = 'fa fa-' . $icon;
        }

        parent::__construct($id, null, $icon);
        $this->addClass($extraClass);

        if ($onClick)
        {
            $this->click($onClick);
        }
    }

}
