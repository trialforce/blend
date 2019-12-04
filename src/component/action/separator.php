<?php

namespace Component\Action;

/**
 * A separator action, used to put html content inside edit action-list
 */
class Separator extends \Component\Action\Action
{

    public function __construct($id = null, $content = null, $class = null, $group = null)
    {
        parent::__construct($id, null, $content, null, 'action-list-separator ' . $class, null, $group);

        $this->setRenderInEdit(true);
        $this->setRenderInGrid(false);
    }

}
