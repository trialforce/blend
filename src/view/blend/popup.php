<?php

namespace View\Blend;

use View\Div;
use DataHandle\Config;

/**
 * Simple popup class.
 */
class Popup extends Div
{

    /**
     * Outer div
     * @var \View\Div
     */
    protected $outter;

    /**
     * Inner div
     * @var \View\Div
     */
    protected $inner;

    /**
     * Popup header
     * @var \View\Div
     */
    public $header;

    /**
     * Popup footer
     * @var \View\Div
     */
    public $footer;

    /**
     * Popup body
     * @var \View\Div
     */
    public $body;

    /**
     * Popup title
     *
     * @var string
     */
    protected $title;

    /**
     * Font-awesome icon
     *
     * @var string
     */
    protected $icon;

    /**
     * Construct a popup.
     *
     * @param string $id
     * @param \View\View $title
     * @param \View\View $body
     * @param \View\View $footer
     * @param string $class
     */
    public function __construct($id = NULL, $title = NULL, $body = NULL, $footer = NULL, $class = NULL)
    {
        parent::__construct($id, NULL, "popup container hide");
        $this->addClass($class);
        $this->append(new Div(NULL, NULL, 'background full'));

        $this->header = new Div(NULL, NULL, 'header');
        $this->body = new Div(NULL, $body, 'body clearfix');
        $this->footer = new Div(NULL, $footer, 'footer clearfix');

        $views[] = $this->header;
        $views[] = $this->body;
        $views[] = $this->footer;

        $this->inner = new Div(NULL, $views, 'inner');
        $this->outter = new Div(NULL, $this->inner, 'outter');

        $this->setTitle($title);

        $this->append($this->outter);
    }

    function getTitle()
    {
        return $this->title;
    }

    function getIcon()
    {
        return $this->icon;
    }

    function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    function setIcon($icon)
    {
        $this->icon = $icon;

        return $this;
    }

    /**
     * Show the popup.
     * Define default response
     */
    public function show($param = FALSE)
    {
        $this->generateTitle();
        Config::set('responseType', 'append');
        \App::addJs(self::getJs('show', $this->getId()));

        return $this;
    }

    /**
     * Close the popup, keeping it in html.
     */
    public function close()
    {
        \App::addJs(self::getJs('close', $this->getId()));
        return $this;
    }

    /**
     * Destroy popup removing it from html.
     */
    public function destroy()
    {
        \App::addJs(self::getJs('destroy', $this->getId()));
        return $this;
    }

    /**
     * Destroy popup removing it from html.
     */
    public static function delete($id = NULL)
    {
        \App::addJs(self::getJs('destroy', $id));
    }

    /**
     * Define the title of popup.
     * Also make the X button.
     *
     * @param mixed $title
     * @return \View\Blend\Popup
     */
    public function generateTitle()
    {
        $headerContent = null;
        $icon = $this->getIcon();

        if ($icon)
        {
            $headerContent[] = new \View\Ext\Icon($icon);
        }

        $this->header->html($headerContent, $this->title);

        $id = $this->getId();

        $close = new \View\Ext\Icon('times', 'black');
        $close->setId('btbClosePopup')
                ->css('float', 'right')
                ->click(self::getJs('destroy', $id))
                ->setTitle('Fechar');

        $this->header->append($close);

        return parent::setTitle(strip_tags($this->title));
    }

    /**
     * Add maximize button to popup
     *
     * @return \View\Blend\Popup
     */
    public function addMaximizeButton()
    {
        $maximize = new \View\Ext\Icon('square-o', 'black');
        $maximize->setId('btbMaximizePopup')
                ->css('float', 'right')
                ->click(self::getJs('maximize', $this->getId()))
                ->setTitle('Aumentar');

        $this->header->append($maximize);

        return $this;
    }

    /**
     * Return a js code from some function of popup
     *
     * @param string $function
     * @param string $id
     * @param boolean  $return
     * @return string
     */
    public static function getJs($function, $id = NULL, $return = FALSE)
    {
        $return = $return ? 'return ' : '';

        if ($id)
        {
            return $return . "popup('{$function}','#{$id}');";
        }
        else
        {
            return $return . "popup('{$function}');";
        }
    }

    /**
     * Create a simple prompt popup
     *
     * @return \View\Blend\Popup
     */
    public static function prompt($title, $question, $okAction, $cancelAction = NULL, $class = NULL)
    {
        if (is_null($cancelAction))
        {
            $cancelAction = \View\Blend\Popup::getJs('destroy', 'prompt');
        }

        $buttons[0] = new \View\Ext\Button('nao', 'cancel', 'Não', $cancelAction);
        $buttons[1] = new \View\Ext\Button('ok', 'check', 'Sim', $okAction, 'primary');
        $buttons[1]->setAutoFocus();
        $buttons[1]->focus();

        $popup = new \View\Blend\Popup('prompt', $title, $question, $buttons, 'prompt no-overflow' . $class);
        $popup->setIcon('question');

        return $popup;
    }

    /**
     * Alert
     *
     * @param sting $title
     * @param mixed $content
     * @param string $closeAction
     * @return \View\Blend\Popup
     */
    public static function alert($title, $content, $closeAction = NULL)
    {
        if (is_null($closeAction))
        {
            $closeAction = \View\Blend\Popup::getJs('destroy');
        }

        $buttons[] = new \View\Ext\Button('close', 'cancel', 'Fechar', $closeAction);

        $popup = new \View\Blend\Popup('alert', $title, $content, $buttons);

        return $popup;
    }

    /**
     * Create a fiedlayout dialog
     *
     * @param \Fieldlayout\Vector $layout
     * @param string $okAction
     * @param string $closeAction
     * @return \View\Blend\Popup
     */
    public static function fieldLayoutDialog(\Fieldlayout\Vector $layout, $okAction, $closeAction = NULL)
    {
        //call onCreate
        $fields = $layout->onCreate();
        $model = $layout->getModel();
        $id = $model->getId();

        if (is_null($closeAction))
        {
            $closeAction = \View\Blend\Popup::getJs('destroy');
        }

        $buttons[] = new \View\Ext\Button('btnSalvaPopup', 'save', 'Salvar', $okAction, 'info');
        $buttons[] = new \View\Ext\Button('nao', 'cancel', 'Cancelar', $closeAction);

        $idPopup = str_replace('\\', '_', $model->getName() . '_popup');

        if (is_numeric($id))
        {
            $label = 'Editar <b>' . lcfirst($model->getlabel()) . '</b>';
        }
        else
        {
            $label = 'Adicionar <b>' . lcfirst($model->getlabel()) . '</b>';
        }

        $popup = new \View\Blend\Popup($idPopup, $label, $fields, $buttons, 'no-overflow');
        $popup->layout = $layout;
        $popup->setIcon('edit');

        return $popup;
    }

}
