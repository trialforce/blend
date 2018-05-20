<?php

namespace View\Ext;

/**
 * Html Editor
 */
class HtmlEditor extends \View\TextArea
{

    public function __construct( $idName = NULL, $value = NULL )
    {
        parent::__construct( $idName, $value );
    }

    public function setContain( \View\View $contain )
    {
        $ok = parent::setContain( $contain );

        $this->makeHtmlEditor();

        return $ok;
    }

}
