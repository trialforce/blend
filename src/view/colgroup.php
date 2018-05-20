<?php

namespace View;

/**
 * Table Column Group
 */
class ColGroup extends \View\View
{

    public function __construct( $id = \NULL, $innerHtml = \NULL, $class = \NULL, $father = \NULL )
    {
        parent::__construct( 'colgroup', $id, $innerHtml, $class, $father );
    }

}
