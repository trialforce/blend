<?php

namespace View;

class Audio extends \View\View
{

    /**
     * Construct the audio
     *
     * @param string $id
     * @param string $src
     */
    public function __construct( $id, $src = NULL, $controls = NULL, $autoplay = NULL, $loop = NULL )
    {
        parent::__construct( 'audio', NULL, NULL );
        $this->setId( $id )->setAttribute( 'src', $src );
    }

    /**
     * Return controls
     *
     * @return string
     */
    public function getControls()
    {
        return $this->getAttribute( 'controls' );
    }

    /**
     * Set if is to show controls
     *
     * @param string $controls
     * @return Audio
     */
    public function setControls( $controls )
    {
        return $this->setAttribute( 'controls', $controls );
    }

    /**
     * Return if is to autoplay the sound
     *
     * @return string
     */
    public function getAutoplay()
    {
        return $this->getAttribute( 'autoplay' );
    }

    /**
     * Define if is to autoplay
     *
     * @param string $autoplay
     * @return Audio
     */
    public function setAutoplay( $autoplay )
    {
        return $this->setAttribute( 'autoplay', $autoplay );
    }

    /**
     * Return if is to loop sound
     *
     * @return string
     */
    public function getLoop()
    {
        return $this->getAttribute( 'loop' );
    }

    /**
     * Define if is to loop sound
     *
     * @param string $loop
     * @return Audio
     */
    public function setLoop( $loop )
    {
        return $this->setAttribute( 'loop', $loop );
    }

    /**
     * Play some url sound.
     * Works with ajax
     *
     * @param string $src
     */
    public static function playSoundOnce( $src )
    {
        \App::addJs( "var audio = new Audio('{$src}'); audio.play();" );
    }

}
