<?php
namespace View;
/**
 * Master page padrão
 * //TODO não deveria estar em \View\
 */
abstract class Master extends \View\Page
{

    /**
     * Pode padrão não são chamados eventos da página mestre
     *
     * @return boolean
     */
    public function callEvent()
    {
        return TRUE;
    }

    /**
     * Le e aplica o layout
     * @param string $layoutPath
     */
    public function setLayoutFile( $layoutPath )
    {
        parent::setLayoutFile( $layoutPath );
        $this->addStyle();
        $this->addJs();
    }

    /**
     * Adiciona os javascripts
     */
    protected function addJs()
    {
        // jQuery
        $jQueryUrl = 'lib/js/jquery.min.js';

        //para casos que é necessário usar 100% local
        if ( defined( 'JQUERY_URL' ) )
        {
            $jQueryUrl = JQUERY_URL;
        }
        elseif ( defined( 'URL_BASE' ) )
        {
            $jQueryUrl = URL_BASE . '/' . $jQueryUrl;
        }

        $this->addScript( $jQueryUrl );

        // Min JS
        $minJs = \Disk\File::getFromStorage( '/min.js' );
        $minJsUrl = $minJs->getUrl() . '?v=' . $minJs->getMTime();
        $this->addScript( $minJsUrl, NULL, \View\Script::TYPE_JAVASCRIPT, 'minjs' );

        /* força o navegador a recarregar o js e o css */
        if ( \Server::getInstance()->isAjax() )
        {
            \App::addJs( "$('#minjs').attr('src','$minJsUrl');" );
        }
    }

    /**
     * Adiciona estilos
     */
    protected function addStyle()
    {
        if ( defined( 'DESENVOLVIMENTO' ) && DESENVOLVIMENTO == TRUE )
        {
            $this->addStyleShet( 'lib/css/bootstrap.css' );
            $this->addStyleShet( 'theme/' . THEME . '/font-awesome.min.css' );
            $this->addStyleShet( 'lib/css/datepicker.css' );
            //$this->addStyleShet( 'lib/css/select2.css' );
            $this->addStyleShet( 'theme/' . THEME . '/select2.css' );
            $this->addStyleShet( 'lib/css/lib.css' );
            $this->addStyleShet( 'theme/' . THEME . '/index.css', 'text/css', 'all', 'mincss' );
            $this->addStyleShet( 'theme/' . THEME . '/celular.css', 'text/css', 'screen and (max-width:480px)', 'celular' );
        }
        else
        {
            $minCss = \Disk\File::getFromStorage( 'min.css' );
            $minCssUrl = $minCss->getUrl() . '?v=' . $minCss->getMTime();
            $this->addStyleShet( $minCssUrl, 'text/css', 'all', 'mincss' );

            //adiciona estilo para celular
            $celularCss = 'theme/' . THEME . '/celular.css';

            if ( file_exists( $celularCss ) )
            {
                $celularCssUrl = $celularCss . '?v=' . filemtime( $celularCss );
                $this->addStyleShet( $celularCssUrl, 'text/css', 'screen and (max-width:480px)', 'celular' );
            }

            /* força o navegador a recarregar o js e o css */
            if ( \Server::getInstance()->isAjax() )
            {
                \App::addJs( "$('#mincss').attr('href','$minCssUrl');" );
            }
        }
    }

    public abstract function onCreate();
}