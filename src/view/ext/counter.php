<?php
namespace View\Ext;
class Counter extends \View\Div
{
    public $seconds;
    public $callbackFunction;

    /**
     *
     * @param \View\Layout $dom
     * @param string $id id do contador
     * @param int $seconds quantos segundos o contador vai contar
     * @param callable $callbackFunction método que será chamado quando o contador chegar a 0
     * @param string $class
     */
    public function __construct( $id, $seconds, $callbackFunction, $class = NULL )
    {
        $this->seconds = $seconds;
        $this->callbackFunction = $callbackFunction;

        parent::__construct( $id, $this->seconds, $class );

        $this->setData( 'seconds', $this->seconds );
        $this->init( $id, $callbackFunction );
    }

    /**
     * Metodo responsável por chamar o método que irá inicializar o contador.
     *
     * @param type $id
     * @param type $callbackFunction
     */
    protected function init( $id, $callbackFunction )
    {
        //FIX-ME fazer com que esse método seja chamado só uma vez.
        //Em requisições ajax tem problema.
        //Método responsável por iniciar o contador.
        $initFunction = '/**
         * Método que inicia o contador.
         *
         * @param string idCounter
         * @param callable func
         * @returns void
         */
        function initCounter(idCounter, func)
        {
            var functionCounter = function()
            {

                //Obtem os segundos
                var second = $(\'#\' + idCounter).attr(\'data-seconds\');
                //Subtrai 1 segundo
                second--;
                //Atualiza a informação do segundo
                $(\'#\' + idCounter).attr(\'data-seconds\', second);
                $(\'#\' + idCounter).html(second);

                //Quando contador chegar a zero
                if (second === 0)
                {
                    //Remove a chamada de subtração contador
                    clearInterval(counterInterval);
                    //Executa função quando o contador chegar ao fim
                    func();
                }
            };

            //Define o intervalo de um segundo para atualizar o contador.
            var counterInterval = setInterval(functionCounter, 1000);
       }';

        //Carrega o método responsável por atualizar o contador.
        \App::addJs( $initFunction );

        //Quando o documento terminar de carregar inicia o contador.
        \App::addJs( '$( document ).ready( initCounter(\'' . $id . '\',function(){' . $callbackFunction . '}))' );
    }

}