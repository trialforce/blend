<?php

namespace Component\Timeline;

/**
 * Simple Timeline item implementation
 */
class Item implements DataItem
{
    public $dateTime;
    public $icon;
    public $title;
    public $content;
    public $link;

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
}