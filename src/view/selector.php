<?php
namespace View;
/**
 * Represents a jquery selector
 */
class Selector extends \View\View
{

    /**
     * Create a seletor jquery
     * @param \View\Layout $dom
     * @param string $selector
     */
    public function __construct( $selector )
    {
        str_replace( array( ' ', '#' ), array( \View\View::REPLACE_SPACE, \View\View::REPLACE_SHARP ), $selector );
        parent::__construct( 'div', $selector );
        //define saída js
        $this->setOutputJs( TRUE );
        //remove do dom para não reaparecer
        $this->remove();
    }

    /**
     * Create um seletor jquery
     * @param \View\Layout $dom
     * @param string $selector
     */
    public static function get( $selector )
    {
        return new \View\Selector( $selector );
    }

}