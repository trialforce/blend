<?php

namespace View;

/**
 * Stylesheet link
 */
class Link extends \View\View
{

    public function __construct( $id = NULL, $href = NULL, $rel = 'stylesheet', $type = 'text/css', $media = 'all' )
    {
        parent::__construct( 'link' );
        $this->setAttribute( 'id', $id );
        $this->setAttribute( 'rel', $rel )->setAttribute( 'href', $href );
        $this->setAttribute( 'type', $type )->setAttribute( 'media', $media );
    }

}
