<?php

namespace Component\Action;

/**
 * A default action that call a page url/method
 */
class Page extends \Component\Action\Action
{

    protected $page;
    protected $event;

    public function __construct($page = null, $event = null, $pk = null, $icon = null, $label = null, $class = null)
    {
        $pageClass = \App::getPageClassFromUrl($page);

        if (class_exists($pageClass))
        {
            $id = str_replace('/', '-', $page);
            parent::__construct('btn-action-' . $id . '-' . $event, $icon, $label, null, $class);

            $this->setPage($page);
            $this->setEvent($event);
            $this->setPk($pk);
        }
        else
        {
            parent::__construct();
        }
    }

    public function getUrl()
    {
        return "p('" . $this->getPage() . '/' . $this->getEvent() . '/' . $this->getPk() . "');";
    }

    public function getPage()
    {
        return $this->page;
    }

    public function getEvent()
    {
        return $this->event;
    }

    public function setPage($page)
    {
        $this->page = $page;
        return $this;
    }

    public function setEvent($event)
    {
        $this->event = $event;
        return $this;
    }

}
