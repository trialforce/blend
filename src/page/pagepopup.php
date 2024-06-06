<?php

namespace Page;

use DataHandle\Request;

/**
 * Trait that allow pages do work inside a Popup
 */
trait PagePopup
{

    /**
     * Repass extra parameters
     * @return string
     */
    private static function getExtraParametersForIFrame()
    {
        $url = '';
        $get = \DataHandle\Get::getInstance();
        $get->remove('p')->remove('e')->remove('v');
        $get = (array) $get;

        if (count($get) > 0)
        {
            $url .= '&' . http_build_query($get);
        }

        return $url;
    }

    public function listarPopup()
    {
        \App::dontChangeUrl();
        $isAjax = \DataHandle\Server::getInstance()->isAjax();
        $url = $this->getPageUrl() . '/listar/?';
        $url .= static::getExtraParametersForIFrame();

        if (!$isAjax)
        {
            \App::redirect($url, true);
            return false;
        }

        $url .= '&iframe=true&rand=' . rand();

        $title = isset($this->model) ? ucfirst($this->model->getLabel()) : 'Listar';
        $this->crudEditPopup($url, $title);

        return true;
    }

    /**
     * Open a popup of this crud.
     * It uses a internal iframe soluction to avoind mixing the forms post and values.
     *
     * @return bool
     * @throws \Exception
     */
    public function editarPopup()
    {
        \App::dontChangeUrl();
        $isAjax = \DataHandle\Server::getInstance()->isAjax();
        $idInput = Request::get('idInput');
        $id = Request::get('v');
        //edit or add
        $url = $id ? $this->getPageUrl() . '/editar/' . $id : $this->getPageUrl() . '/adicionar/?';
        $url .= static::getExtraParametersForIFrame();

        if (!$isAjax)
        {
            return \App::redirect($url, true);
        }

        //iframe and rand (to avoid browser cache)
        $url .= '&iframe=true&rand=' . rand();

        $title = ucfirst(($id ? 'editar' : 'adicionar') . ' ' . lcfirst($this->model->getLabel()));

        $this->crudEditPopup($url, $title, $idInput);
        return true;
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function verPopup()
    {
        \App::dontChangeUrl();
        $isAjax = \DataHandle\Server::getInstance()->isAjax();
        $idInput = Request::get('idInput');
        $id = Request::get('v');
        $url = $this->getPageUrl() . '/ver/' . $id . '?';
        $url .= static::getExtraParametersForIFrame();

        if (!$isAjax)
        {
            return \App::redirect($url, true);
        }

        //iframe and rand (to avoid browser cache)
        $url .= '&iframe=true&rand=' . rand();

        $title = ucfirst('Ver ' . lcfirst($this->model->getLabel()));
        $this->crudEditPopup($url, $title, $idInput);

        return true;
    }

    /**
     * Open a popup of this crud, to add while referencing parent.
     * It uses a internal iframe solution to avoid mixing the forms post and values.
     */
    public function adicionarPopup()
    {
        \App::dontChangeUrl();
        $isAjax = \DataHandle\Server::getInstance()->isAjax();
        $idInput = Request::get('idInput');
        $idParent = Request::get('v');

        // add referencing parent
        $url = $this->getPageUrl() . '/adicionar/' . $idParent . '?';
        $url .= static::getExtraParametersForIFrame();

        if (!$isAjax)
        {
            return \App::redirect($url, true);
        }

        $url .= '&iframe=true&rand=' . rand();

        $title = ucfirst('adicionar' . ' ' . lcfirst($this->model->getLabel()));

        $this->crudEditPopup($url, $title, $idInput);
    }

    /**
     * @param $url
     * @param $title
     * @param $idInput
     * @return void
     * @throws \Exception
     */
    public function crudEditPopup($url, $title, $idInput = null)
    {
        $body = new \View\IFrame('edit-popup-iframe', $url);
        $body->setWidth('100', '%')->setHeight('70', 'vh');
        $buttons = null;

        $titleLink = new \View\A('popup-title', $title, $this->getPageUrl(), null, null);
        $titleLink->setTitle($title);

        $popup = new \View\Blend\Popup('edit-popup', $titleLink, $body, $buttons, 'popup-full-body form ' . $this->getPageUrl());
        $popup->setIcon($this->icon);
        $popup->footer->remove();
        $popup->show();

        //allow to update the original input if needed
        if ($idInput)
        {
            $this->byId('btbClosePopup')->click("comboModelClose('$idInput')");
        }
        else
        {
            if (stripos($url, 'ver') > 0)
            {
                $this->byId('btbClosePopup')->click("popup('destroy','#edit-popup');");
            }
            else
            {
                $this->byId('btbClosePopup')->click("p(window.location.href); popup('destroy','#edit-popup');");
            }
        }
    }
}