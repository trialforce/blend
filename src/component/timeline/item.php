<?php

namespace Component\Timeline;

/**
 * Simple Timeline item implementation
 */
class Item implements DataItem
{
    public $dateTime;
    public $icon = null;
    public $title;
    public $content = null;
    public $link = null;
    public $color = null;

    public function getTimelineDateTime()
    {
        return $this->dateTime;
    }

    public function getTimelineIcon()
    {
        return $this->icon;
    }

    public function getTimelineTitle()
    {
        return $this->title;
    }

    public function getTimelineContent()
    {
        return $this->content;
    }

    public function getTimelineLink()
    {
        return $this->link;
    }

    public function getTimelineColor()
    {
        return $this->color;
    }
}