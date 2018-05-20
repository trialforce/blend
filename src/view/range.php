<?php
namespace View;
/**
 * Campo para escolher um valor inteiro dentro de um intervalo.
 */
class Range extends \View\Input
{

    /**
     * Construtor do range definindo informações do input e a distância do intervalo.
     * @param type $min valor inicial do intervalo
     * @param type $max valor final do intervalo
     */
    public function __construct( $idName = NULL, $value = null, $min = 0, $max = 100 )
    {
        parent::__construct( $idName, \View\Input::TYPE_RANGE, $value );
        $this->setMin( $min );
        $this->setMax( $max );
    }

    public function getMin()
    {
        return $this->getAttribute( 'min' );
    }

    public function setMin( $min )
    {
        $this->setAttribute( 'min', $min );

        return $this;
    }

    public function getMax()
    {
        return $this->getAttribute( 'max' );
    }

    public function setMax( $max )
    {
        $this->setAttribute( 'max', $max );

        return $this;
    }

    public function getStep()
    {
        return $this->getAttribute( 'step' );
    }

    /**
     * Define de quanto em quanto o slider moverá.
     * @param type $step
     */
    public function setStep( $step )
    {
        $this->setAttribute( 'step', $step );

        return $this;
    }

}