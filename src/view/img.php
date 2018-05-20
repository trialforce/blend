<?php
namespace View;
/**
 * Html image element
 */
class Img extends \View\View
{

    public function __construct( $idName = NULL, $src = NULL, $width = null, $height = null, $alt = NULL, $class = NULL )
    {
        parent::__construct( 'img', $idName );
        $this->setAttribute( 'src', $src )->setAttribute( 'width', $width )->setAttribute( 'height', $height );
        $this->setAttribute( 'alt', $alt )->setAttribute( 'title', $alt )->setClass( $class );
    }

    /**
     * List know images extensions
     *
     * @return array
     *
     */
    public static function knowExtensions()
    {
        return array( 'jpg', 'jpeg', 'gif', 'png' );
    }

}