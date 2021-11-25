<?php

namespace View\Ext;

class Accordion extends \View\Div
{

    protected $head;
    protected $body;

    public function __construct($id = \NULL, $head = \NULL, $content = \NULL, $class = 'col-12', $father = \NULL)
    {
        parent::__construct($id, null, 'accordion ' . $class, $father);

        $title = new \View\Div($id . '-title', $head, 'accordion-title');
        $title->click($this->getOnClick());

        $this->head = new \View\Div($id . '-head', [$title, $this->createIcon()], 'accordion-head', $this);

        $this->body = new \View\Div($id . '-body', $content, 'accordion-body', $this);
    }

    protected function getOnClick()
    {
        //add support for crap grid id's with \\
        $idJs = str_replace('\\', '\\\\', $this->getId());
        $onclick = "return blend.accordion.toggle('{$idJs}');";
        return $onclick;
    }

    public function open()
    {
        $this->css('height', 'auto');
        $this->addClass('accordion-open');
    }

    public function close()
    {
        $this->removeClass('accordion-open');
    }

    public function createIcon()
    {
        return new \View\Ext\Icon(' accordion-icon', $this->getId() . '-icon', $this->getOnClick());
    }

    public function getHead()
    {
        return $this->head;
    }

    public function setHead($head)
    {
        $this->head = $head;
        return $this;
    }

    public function getBody()
    {
        return $this->body;
    }

    public function setBody($body)
    {
        $this->body = $body;
        return $this;
    }

    /**
     * Open/close/toggle any accordion without having php object
     *
     * @param string $id
     */
    public static function visibility($id, $action = 'open')
    {
        \App::addJs("blend.accordion.{$action}('{$id}');");
    }

}
