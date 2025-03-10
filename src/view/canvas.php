<?php

namespace View;

/**
 * Html 5 canvas elemento
 */
class Canvas extends \View\View
{

    /**
     * Construct a canvas
     * @param string $id
     * @param int $width
     * @param int $height
     * @param string $class
     */
    public function __construct( $id, $width, $height, $class = \NULL )
    {
        parent::__construct( 'canvas', NULL, NULL, $class );
        $this->setId( $id );
        $this->setWidth( $width );
        $this->setHeight( $height );
    }

    /**
     * Set height
     * @param int $height
     * @return \View\Canvas
     */
    public function setHeight( $height, $unit = 'px' )
    {
        return $this->setAttribute( 'height', intval( $height ) );
    }

    /**
     * Return canvas height
     *
     * @return int
     */
    public function getHeight()
    {
        return intval( $this->getAttribute( 'height' ) );
    }

    /**
     * Define the canvas width
     *
     * @param int $width
     * @return \View\Canvas
     */
    public function setWidth( $width , $unit = 'px' )
    {
        return $this->setAttribute( 'width', intval( $width ) );
    }

    /**
     * Return the canvas width
     *
     * @return int
     */
    public function getWidth()
    {
        return intval( $this->getAttribute( 'width' ) );
    }

}
