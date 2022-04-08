<?php

namespace View\Ext;

/**
 * Html Editor
 * Class used as retro compatibility
 */
class HtmlEditor extends \View\Ext\ContentEditable
{

    public function __construct($idName = NULL, $value = NULL)
    {
        parent::__construct($idName, $value);
    }

    public function setContain(\View\View $contain)
    {
        $ok = parent::setContain($contain);

        return $ok;
    }

}
