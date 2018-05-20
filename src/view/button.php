<?php
namespace View;
/**
 * Html button element
 */
class Button extends \View\View
{
    /**
     * Tipo postar
     */
    const TYPE_SUBMIT = 'submit';
    const TYPE_BUTTON = 'button';

    public function __construct( $idName = \NULL, $label = \NULL, $onClick = \NULL, $class = \NULL )
    {
        parent::__construct( 'button', $idName, $label, $class );
        $this->click( $onClick )->setType( self::TYPE_BUTTON );
    }

    /**
     * Define o tipo
     *
     * @param type $type
     * @return \Button
     */
    public function setType( $type )
    {
        $this->setAttribute( 'type', $type );

        return $this;
    }

    /**
     * Retorna o tipo
     *
     * @return string
     */
    public function getType()
    {
        return $this->getAttribute( 'type' );
    }

}