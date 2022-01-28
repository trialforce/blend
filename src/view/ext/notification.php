<?php

namespace View\Ext;

/**
 * Simple JS notification
 */
Class Notification
{

    protected $title;
    protected $body;
    protected $link;
    protected $icon;

    public function __construct($title, $body, $link = null, $icon = null)
    {
        $this->title = $title;
        $this->body = $body;
        $this->link = $link;
        $this->icon = $icon;

        \View\View::getDom()->append($this);
    }

    public function __toString()
    {
        $this->body = strip_tags($this->body);
        \App::addJs("blend.notification.new('{$this->title}', '$this->body', '{$this->link}','{$this->icon}');");
        return '';
    }

}
